# 1. Lupin

### Đầu tiên khi vào mỗi bài lab ta sẽ thực hiện Scan để thu thập thông tin

Scan để lấy địa chỉ IP của máy ảo mà ta đang exploit:

![image](https://user-images.githubusercontent.com/86275419/224600679-1de6e122-b333-428a-9a84-0e4c113c194d.png)

Địa chỉ IP máy mục tiêu: `192.168.20.46`

Scan nmap để thu thập thông tin máy mục tiêu:

![image](https://user-images.githubusercontent.com/86275419/224601048-0b80b68a-5117-48f9-8686-246ca878f9bb.png)

Server đang mở hai Port: 22 (SSH), 80 (HTTP)

Ta sẽ tập trung exploit vào dịch vụ web để tìm cách RCE hoặc tìm thông tin để bem vào server 

## Web 

![image](https://user-images.githubusercontent.com/86275419/224601486-b9a3f637-df13-4ad1-ac3e-8bdea1dcd777.png)

Bùm, một trang web không có chức năng gì :))

Theo thói quen thì mình sẽ thực hiện scan subdir trước

Fuzz bằng Dirsearch là đơn giản nhất:

![image](https://user-images.githubusercontent.com/86275419/224601905-2a7b78c7-0913-4b58-8095-ee74aef979d7.png)

Tool quét ra được một số thư mục ẩn, nhưng đáng chú ý nhất là file `robots.txt`

![image](https://user-images.githubusercontent.com/86275419/224602586-26b7c2d9-3424-4319-8b18-42eb5fbc4064.png)

Truy cập `~myfiles`:

![image](https://user-images.githubusercontent.com/86275419/224602114-0849c585-4a8d-43ca-bcbb-34a3d2f9de76.png)

Truy cập vào thì trang web hiện `Error 404`, nhưng cách hiển thị này lạ lắm :)). Ta sẽ view source code để xem có gì

![image](https://user-images.githubusercontent.com/86275419/224602352-e2e28e36-488a-437f-92c3-e6a348f53e11.png)

Bùm, chỉ có một lời động viên chứ không có thông tin gì khác

Sau một hồi truy cập các thư mục ẩn thì mình không thu được thông tin gì, bây giờ mình sẽ thực hiện Fuzz lần nữa

![image](https://user-images.githubusercontent.com/86275419/224602996-c1ef0c97-bb0a-476c-8003-1f6687080844.png)

Sau 7749 lần đổi wordlist thì mình vẫn không thu được gì, sau một hồi xem lại mình mới phát hiện ra trick ở bài này là thêm ký tự `~` đằng trước tên file mà tác giả đã gợi ý khi ta truy câp vào `~myfile`

Thực hiện Fuzz lại, nhưng lần này ta sẽ thêm dấu `~`:

![image](https://user-images.githubusercontent.com/86275419/224603525-d48f9788-bc21-469c-b009-8971c638cb2d.png)

Sau khi Fuzz lại, ta thấy có một subdir `~secret`

Ta sẽ thử truy cập

![image](https://user-images.githubusercontent.com/86275419/224603765-8f1eda7f-388e-4d73-a636-82de64c8b2b1.png)

Sau khi đọc nội dung ta thấy rằng cần tìm file private key để có thể SSH vào server

Thực hiện Fuzz để tìm file

![image](https://user-images.githubusercontent.com/86275419/224604426-c9bbcbda-e118-4abf-a4fa-e4e63da75275.png)

![image](https://user-images.githubusercontent.com/86275419/224604568-ad406672-ee67-4f17-8cef-218528659ba9.png)

![image](https://user-images.githubusercontent.com/86275419/224605578-8af02cca-c072-4612-b056-715d95474398.png)
... 

Sau 7749 lần đổi wordlist thì mình cũng méo thu đươc gì, cay :triumph: 

Đến đoạn này mình đang bị stuck, sau đó mình mới biết rằng trước tên file cần thêm dấu `.`

Thực hiện Fuzz lại:

![image](https://user-images.githubusercontent.com/86275419/224605992-61913af4-061e-4825-90b9-491b146a087e.png)

Bùm, một loạt file được tìm thấy, nhưng ta sẽ tập trung vào `.mysecret.txt`

![image](https://user-images.githubusercontent.com/86275419/224606193-8231d486-43c4-45b7-b3fa-4e245996417e.png)

Truy cập file

![image](https://user-images.githubusercontent.com/86275419/224606416-4da0e15f-4dc9-4e3c-bda6-a6bb9356478c.png)

Ta nhận được thông điệp đang bị mã hóa, việc của mình bây giờ là cần giải mã thông điệp này

Mình sẽ sử dụng [dcode.fr](https://www.dcode.fr/cipher-identifier) để xác định loại mã hóa

![image](https://user-images.githubusercontent.com/86275419/224606680-f9f20a11-97cb-4b0c-97af-61d26a78004e.png)

Ta thấy rằng loại mã hóa này được xác định khả năng cao là base58

Decrypt ta thu được SSH Private key:

![image](https://user-images.githubusercontent.com/86275419/224606995-bab8ecf5-1d97-4f30-ba8e-1516c6ec2a07.png)

Lúc nãy khi truy cập vào `~myfiles` thì tác giả có gợi ý là crack passfrase bằng wordlist `fasttrack` nên mình dùng luôn khỏi suy nghĩ

Đầu tiên mình tạo 1 file để chứa private key và đặt tên là id_rsa

![image](https://user-images.githubusercontent.com/86275419/224611088-811fdb8c-66a5-4dc1-b484-f2311160ecf4.png)

Tiếp theo mình chuyển định dạng private key về dạng tool john có thể crack bằng tool ssh2john:

![image](https://user-images.githubusercontent.com/86275419/224611297-74620fe9-5a43-462d-9b08-d73aeecafde5.png)

Ta crack được passfrase: `P@55w0rd!`

Username khi ở `~secret` ta cũng có thể đoán được là `icex64`

## Bây giờ ta sẽ ssh vào server

### Note: muốn sử dụng private key để ssh thì ta phải set quyền file chứa private key bắt buộc là 600

![image](https://user-images.githubusercontent.com/86275419/224612003-b447f3f2-20a1-4a49-86ec-33fd22bc8406.png)

![image](https://user-images.githubusercontent.com/86275419/224612044-fd556f53-ebf5-4d32-b653-e404a20a7f91.png)

## Tiếp theo ta sẽ phải leo lên quyền root

Theo thói quen khi mình vào server thì sẽ thử lệnh `sudo -l` đầu tiên

![image](https://user-images.githubusercontent.com/86275419/224612452-608c94ea-c76b-42aa-b846-633ff6acdbb5.png)

Ngon, có manh mối để ta leo quyền cao hơn rồi. Ý nghĩa của câu lệnh sẽ là: ta sẽ có thể chạy file `/home/arsene/heist.py` bằng `/usr/bin/python3.9` với quyền user  `arsene` mà không cần password khi sudo

Bây giờ ta sẽ xem file `/home/arsene/heist.py` có gì để ta ngịch không

Trước hết ta sẽ xem quyền của file, nếu file này mình mà ghi được thì sẽ rất đơn giản để lên quyền user `arsene`

![image](https://user-images.githubusercontent.com/86275419/224613985-40c2c85c-4de3-4782-980b-cc9d408743e6.png)

Vậy là mình không có quyền ghi vào file này. Giờ mình sẽ xem file này có gì

![image](https://user-images.githubusercontent.com/86275419/224614100-a8be18ac-1fc6-4b2c-a8e1-1624f328e028.png)

Khi đọc code xong mình nghĩ ngay đến vector import thư viện. Mình sẽ kiểm tra xem thứ tự ưu tiên import thư viện của chương trình bằng lệnh `python3 -c 'import sys; print("\n".join(sys.path))'`

![image](https://user-images.githubusercontent.com/86275419/224614422-7ef84b4e-df67-451d-bbe9-17adf9a38f82.png)

Ta sẽ tìm thư viện `webbrowser` trong `/usr/lib/python3.9` để xem có thể sửa được nó không

![image](https://user-images.githubusercontent.com/86275419/224614857-d98dffe0-1997-49c7-b0b6-cf211d63acc8.png)

![image](https://user-images.githubusercontent.com/86275419/224614912-6fea0fed-f5c1-4dd9-8dbd-5dc7065e1d2a.png)

Ngon, file này cho ta full quyền luôn, giờ ta chỉ cần vào sửa chút là sẽ lấy được quyền cao hơn

![image](https://user-images.githubusercontent.com/86275419/224615266-2dc389dc-9042-43de-b414-a25fec5ee7e6.png)

Chạy lệnh trong sudo -l

![image](https://user-images.githubusercontent.com/86275419/224615732-25d1aa16-78a1-4b0e-bafc-7b9e1a7ac18e.png)

Bùm, ta đã lên user arsene

## Bây giờ từ arsene ta sẽ tìm đường lên root

Đầu tiền cứ là `sudo -l`

![image](https://user-images.githubusercontent.com/86275419/224616109-b9c45ff2-8830-47dd-b6ab-4c0cc021fbd2.png)

Ta có thể chạy `pip` dưới quyền root mà không cần password

Bây giờ ta sẽ lên [gtfo](https://gtfobins.github.io/) tìm payload exploit rồi lên root thôi 

![image](https://user-images.githubusercontent.com/86275419/224616340-e5cb75e2-42d4-4c3d-82e4-a854827f2926.png)

Ta có thể chạy lệnh bằng sudo nên sẽ dùng payload này

![image](https://user-images.githubusercontent.com/86275419/224616386-5e06e707-5dab-4b5f-9f5d-f2b52a80e2f0.png)

![image](https://user-images.githubusercontent.com/86275419/224616547-0353c921-020b-43d3-867a-ab8c19d0c0f9.png)

Bùm, lên root thành công 








