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


Sau 7749 lần đổi wordlist thì mình cũng méo thu đươc gì, cay :triumph: 









