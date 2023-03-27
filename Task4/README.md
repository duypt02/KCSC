# C.O.P

### Build Dockerfile

![image](https://user-images.githubusercontent.com/86275419/227902491-e1487d10-5df1-40c5-8d95-adf611ea9454.png)

* Note: lần đầu build nếu fail thì ae build lại là ok nhé. Web mặc định chạy trên port 1337

![image](https://user-images.githubusercontent.com/86275419/227897018-6d2cc8ac-ed92-49ce-a8b0-03f2e6682081.png)

Challenge cho ta một trang web bán hàng có chức năng xem sản phẩm

## Review source code

Web được xây dựng bằng ngôn ngữ Python, do kiến thức code web bằng python không có nhiều nên trong bài này chỗ nào không hiểu mình ném hết lên chatGPT là ra

![image](https://user-images.githubusercontent.com/86275419/227922341-2d78a805-4a93-418b-9d2a-951d18049c0b.png)

Sau khi review source code thì mình cần chú ý vào một số đoạn sau:

![image](https://user-images.githubusercontent.com/86275419/227924263-7af0384b-b1ae-4af6-bc81-0d66a665fb79.png)

Đầu tiên khi chạy app thì nó sẽ tạo database sqlite3 với 4 đối tượng Item được serialize bằng hàm `pickle.dumps(x)` 



