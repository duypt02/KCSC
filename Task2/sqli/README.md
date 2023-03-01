Run command: `docker-compose up`

## Khai thác lỗi SQL Injectionn in-band

### Nơi xảy ra lỗi: Thanh Search

![image](https://user-images.githubusercontent.com/86275419/222053653-48713f4b-b951-434d-81e3-4f858de3f836.png)

### Payload tấn công lấy username, password SQLi Union: 

`%' union select username, NULL, password, NULL, NULL, NULL from users where username like '%`

### Kết quả:

![image](https://user-images.githubusercontent.com/86275419/222054010-f26d357a-45ab-4cba-b556-6bcf6c5690d7.png)

## Khai thác lỗi SQLi Blind

### Nơi xảy ra lỗi: chức năng check user

Nếu điều kiện đúng:

![image](https://user-images.githubusercontent.com/86275419/222054319-64b41f88-c410-4c47-814a-5ab853c8827b.png)

Kết quả:

![image](https://user-images.githubusercontent.com/86275419/222054369-390e69e3-861e-4fc3-928d-bec9d4490ff5.png)

Nếu điều kiện sai:

![image](https://user-images.githubusercontent.com/86275419/222054856-26f95acc-b2e0-438b-9403-bf8191acbee5.png)

Kết quả:

![image](https://user-images.githubusercontent.com/86275419/222054904-54265144-c6ce-4b97-86a1-f448565519c9.png)


### Payload tấn công

Tùy vào từng mục đích mà custom payload cho phù hợp


