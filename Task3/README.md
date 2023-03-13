# 1. Lupin

### Đầu tiên khi vào mỗi bài lab ta sẽ thực hiện Scan để thu thập thông tin

Scan để lấy địa chỉ IP của máy ảo mà ta đang exploit, ở đây mình dùng tool `arp-scan `:

![image](https://user-images.githubusercontent.com/86275419/224600679-1de6e122-b333-428a-9a84-0e4c113c194d.png)

Địa chỉ IP máy mục tiêu: `192.168.20.46`

Scan nmap để thu thập thông tin máy mục tiêu:

![image](https://user-images.githubusercontent.com/86275419/224601048-0b80b68a-5117-48f9-8686-246ca878f9bb.png)

Server đang mở 2 Port (có thể nhiều hơn): 22 (SSH), 80 (HTTP)

Ta sẽ tập trung exploit vào dịch vụ web để tìm cách RCE hoặc tìm thông tin để bem vào server 

## Exploit Web 

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

# 2. Phineas

## Scan

Scan tìm IP:

![image](https://user-images.githubusercontent.com/86275419/224674186-5e43440e-d2a9-432b-8326-b3eaafad6840.png)

IP Server: `192.168.44.117`

Scan thu thập thông tin Server:

![image](https://user-images.githubusercontent.com/86275419/224675545-a0310cdc-34ca-4ac4-b450-690cb3576f7d.png)

Kết quả Scan cho thấy server đang mở 4 port: 22 (SSH), 80 (HTTP), 111 (RPC), 3306 (mysql)

Ta sẽ tập trung exploit vào port 80 (http) vì nó có khả năng dính lỗi nhiều nhất

## Exploit Web

![image](https://user-images.githubusercontent.com/86275419/224677353-bd177546-6f79-4468-973a-880be4a56633.png)

Thực hiện Fuzz subdir:

![image](https://user-images.githubusercontent.com/86275419/224678745-2e037a6d-79f4-4ec1-a0ac-d6915ba85c44.png)

Dùng dirsearch thì mình quét được file đáng chú ý nhất là `cgi-bin` thường có thể dính `ShellShock`, tiếp tục quét subdir của cgi-bin

![image](https://user-images.githubusercontent.com/86275419/224679638-73e48cb1-4f77-436b-8998-53fc6e17b200.png)

![image](https://user-images.githubusercontent.com/86275419/224679692-5772f097-d2ec-4409-a91f-41e191ea1b80.png)

Quét 2 lần như trên mình không thu được gì nên mình sẽ Fuzz lại từ thư mục chính 

![image](https://user-images.githubusercontent.com/86275419/224680141-c77185a3-e685-4182-a5a1-0f86673bae32.png)

Lần này mình quét được subdir `structure`

![image](https://user-images.githubusercontent.com/86275419/224680196-e0cbb41c-319f-4730-ae11-3b628aecf3da.png)

Truy cập vào `structure`

![image](https://user-images.githubusercontent.com/86275419/224680694-1e87ed4d-97dc-4c32-a86a-ef4010bded10.png)

Trang web không có chức năng gì, view source code cũng không có manh mối gì nên mình sẽ thử Fuzz tiếp

![image](https://user-images.githubusercontent.com/86275419/224682504-770e2e4e-ef59-40a9-8088-d622153417ad.png)

Kết quả

![image](https://user-images.githubusercontent.com/86275419/224682666-4f60d270-a5af-4623-828e-25e79500e62f.png)

Trong đống này thì chỉ có robots.txt là có giá trị

![image](https://user-images.githubusercontent.com/86275419/224684648-a25d0469-9465-4b86-b19f-3e0ac31ea35e.png)

Truy cập `fuel`

![image](https://user-images.githubusercontent.com/86275419/224684751-ec6f9460-324b-42dd-b2ba-f8199367a342.png)

hmm, not found

Ta sẽ thêm index.php vào trước fuel

![image](https://user-images.githubusercontent.com/86275419/224686205-e452eacc-5076-45ac-9bd0-ba4b28ec0b66.png)

Ta vào được trang login của một CMS. Trong số những lab mình đã làm mà gặp mấy cái CMS như này thì 100% dính CVE

Ta sẽ đi tìm phiên bản của CMS rồi lên mạng search PoC exploit thôi 

![image](https://user-images.githubusercontent.com/86275419/224687646-88d8b91a-086f-418c-b1b4-484e9fc3a8b7.png)

FUEL 1.4

Có [PoC](https://www.exploit-db.com/exploits/50477) giờ ta chỉ cần tải về chạy lấy shell thôi

![image](https://user-images.githubusercontent.com/86275419/224690040-75a94ca1-03df-4059-9105-1fab5ce26d1d.png)

![image](https://user-images.githubusercontent.com/86275419/224690861-123c626c-534a-4af2-afe0-03efee9104f1.png)

Ok, exploit thành công, giờ ta lấy reverse shell về thôi 

Tạo reverse shell nhanh chóng tại [đây](https://www.revshells.com/)

Trên máy mình mở một port để lấy shell

![image](https://user-images.githubusercontent.com/86275419/224693848-7932f5dd-d3f0-4ec0-874a-467cf8ff42d9.png)

Trên shell của PoC ta chạy revere shell đã tạo bên trên

![image](https://user-images.githubusercontent.com/86275419/224692164-00dc00af-3027-40f5-82f7-6ae42fb23b54.png)

![image](https://user-images.githubusercontent.com/86275419/224693810-38289c4b-8174-40a9-9674-c1407d670f3e.png)

Lấy reverse shell về thành công

Shell khi lấy về chưa tty nên mình sẽ lên tty trước

![image](https://user-images.githubusercontent.com/86275419/224694069-d06ce41c-714f-47d4-a586-4b2409506590.png)

Vậy là không lên tty được, đồng nghĩa với việc mình không sudo được -> không `sudo -l`

Nêu không sudo được thì mình sẽ đi tìm manh mối trong server

Đầu tiên xem có file nào trong crontab có thể nghịch vào không 

![image](https://user-images.githubusercontent.com/86275419/224694952-d461b747-b067-4f1f-9a70-67e9b5232f87.png)

Rồi xong, không cho đọc luôn

Tiếp theo mình sẽ vào source code của web để xem có thông tin gì có thể sử dụng không

Sau một hồi đọc một đống file thì mình tìm được một dữ liệu đáng chú ý

![image](https://user-images.githubusercontent.com/86275419/224696046-189b3e3d-42b6-4568-b45f-d8d05da4c066.png)

Với thông tin này thì mình có thể log vào mysql, hoặc có thể là hint để ssh vào user `anna`

Mình đã thử log mysql nhưng không được, vậy thì chỉ có thể là trường hợp còn lại

![image](https://user-images.githubusercontent.com/86275419/224696898-2e4686a9-42c6-45dd-a23f-7c9bc9a66c72.png)

Đúng luôn, ta đã ssh được vào user `anna`

![image](https://user-images.githubusercontent.com/86275419/224697348-06fe3368-47c4-4b29-b3b6-6a3ed9d359ed.png)

Bây giờ ta cần exploit để lên quyên cao hơn

Trước hết cứ `sudo -l`

![image](https://user-images.githubusercontent.com/86275419/224697630-344b7ebb-948b-4664-95a0-08ff61d4c9d9.png)

-> Không sử dụng được cách này

Sau một hồi thử các cách hay làm thì mình không thu được gì nên mình sẽ chạy `linpeas` để tìm thêm manh mối 

![image](https://user-images.githubusercontent.com/86275419/224700866-02e63e3b-7842-4a71-a020-33d2b6310e99.png)

Sau khi chạy xong mình mới phát hiện ra đã bỏ qua directory `web` trong thư mục chính của user anna

Thử vào xem có gì có thể khai thác không

![image](https://user-images.githubusercontent.com/86275419/224701522-7765dbb5-9f9a-4163-b1da-231575ee377b.png)

file app.py

![image](https://user-images.githubusercontent.com/86275419/224702363-dca8a56f-68d7-40a3-801d-4bbbbece631d.png)

Sau khi đọc source thì mình hiểu nôm na là ứng dụng sẽ gửi dữ liệu mình truyền vào bằng `post` method tới `/heaven` để xử lý nhưng mình không biết chức năng của `pickle` là gì nên đã lên mạng xem thử và biết được rằng đây là module serialization trong python. Mà serialization thì có khả năng đựa vào đây để khai thác là rất cao 

Và mình tìm được bài viết [này](https://github.com/CalfCrusher/Python-Pickle-RCE-Exploit)

Process khả năng chạy chương trình dưới quyền root, nên ta chỉ cần chạy được revese shell là sẽ có shell của root

![image](https://user-images.githubusercontent.com/86275419/224705865-e6797dcd-891a-45f7-b676-d75503355b92.png)

Giờ mình sẽ tải PoC bên trên custom lại chạy xem có được không

![image](https://user-images.githubusercontent.com/86275419/224706904-9f2ca67c-1eaa-4841-834b-602d5aae9632.png)

Custom file PoC

![image](https://user-images.githubusercontent.com/86275419/224709069-4d8205f2-1983-467f-8bd6-4098d6ac1145.png)

Mở port nhận reverse shell

![image](https://user-images.githubusercontent.com/86275419/224709395-edd0bd9c-5777-4401-a8a1-799c8556ebc3.png)

Chạy PoC

![image](https://user-images.githubusercontent.com/86275419/224709461-59a4fd93-20e1-4782-bc3d-06115e36eebd.png)

![image](https://user-images.githubusercontent.com/86275419/224709597-79b43c60-517f-48e2-ae50-7d4317f6a4bc.png)

Bùm, lên root thành công

![image](https://user-images.githubusercontent.com/86275419/224709808-6331fedb-138e-4778-a562-add3ab65d209.png)




