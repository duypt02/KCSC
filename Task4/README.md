# C.O.P

### Build Dockerfile

![image](https://user-images.githubusercontent.com/86275419/227902491-e1487d10-5df1-40c5-8d95-adf611ea9454.png)

* Note: lần đầu build nếu fail thì ae build lại là ok nhé. Web mặc định chạy trên port 1337

![image](https://user-images.githubusercontent.com/86275419/227897018-6d2cc8ac-ed92-49ce-a8b0-03f2e6682081.png)

Challenge cho ta một trang web bán hàng có chức năng xem sản phẩm

## Phân tích source code

Web được xây dựng bằng ngôn ngữ Python, do kiến thức code web bằng python không có nhiều nên trong bài này chỗ nào không hiểu mình ném hết lên chatGPT là ra. EX:

![image](https://user-images.githubusercontent.com/86275419/227953728-aa220781-b0d8-4afb-b86f-2bc532848f09.png)

Sau khi review source code thì mình cần chú ý vào một số đoạn sau:

- File `database.py`:

![image](https://user-images.githubusercontent.com/86275419/227924263-7af0384b-b1ae-4af6-bc81-0d66a665fb79.png)

Đầu tiên khi chạy app thì nó sẽ tạo database sqlite3 và lưu 4 đối tượng Item được serialize bằng hàm `pickle.dumps(x)` 

- File `routes.py`

![image](https://user-images.githubusercontent.com/86275419/227957772-d58b5e79-e5fb-4f6c-9e9e-8151a122730e.png)

Khi vào trang chủ (`/`) thì nó sẽ gọi hàm `index()`. Hàm này sẽ render giao diện từ file `index.html` và gán biến `products` (chứa các đối tượng Item được lưu vào database khi khởi tạo app) vào `index.html`

Khi vào từng sản phẩm (`/view/<product_id>`) thì nó sẽ gọi hàm `product_details(product_id)`. Hàm này sẽ render giao diện từ file `index.html` và gán biến `product` (chứa đối tượng Item với product_id tương ứng) vào `index.html`

- File `models.py`

![image](https://user-images.githubusercontent.com/86275419/227979925-0630961b-c067-492e-aa3d-cc0c07685421.png)

File này chứa các hàm có chức năng truy vấn CSDL

+ Hàm `select_by_id(product_id)` được gọi bởi hàm `product_details(product_id)` bên trên, hàm này có điểm cần lưu ý là `product_id` được gán trực tiếp vào câu truy vấn mà không có bất kì hàm filter nào cả => có thể tấn công SQLi. Một lưu ý nữa là hàm chỉ nhận một câu truy vấn `one=True`, có nghĩa là nếu có thể Injection thì ta chỉ có thể inject các Syntax nối thêm vào câu lệnh gốc để tạo thành 1 câu lệnh SQL duy nhất
+ Hàm `all_products()` được gọi bởi hàm `index()` bên trên

- File `index.html`:

![image](https://user-images.githubusercontent.com/86275419/227983616-291b6ac7-4ad7-4f3e-9f0f-f56c7ecd845f.png)

- Ta sẽ chú ý vào đoạn code Jinja
+ Ở đây sẽ có một vòng lặp lấy từng phần tử của `products` và gán vào `product`, `product` sẽ được deserialize bởi hàm pickle (bên dưới) và gán vào biến item
+ Thân vòng lặp là các thẻ để in ra nội dung của đối tượng được deserialize (item)

- File `item.html`:

![image](https://user-images.githubusercontent.com/86275419/227990161-e05034ef-23b9-4b7d-9551-3a4844bbd82b.png)

File có chức năng hiển thị chi tiết 1 product

Nội dung tương tự file `index.html` nhưng ở đây sẽ chỉ có 1 phần tử `product`

- File `app.py`:

![image](https://user-images.githubusercontent.com/86275419/227994551-4c3db590-58b4-4343-8abe-be7f7a11b32d.png)

Ta sẽ chú ý vào hàm `pickle_loads(s)`
+ Hàm này sẽ chịu trách nhiệm deserialize, ở Task 3 mình đã được làm một bài về Serialization nên có thể nhận định đây khả năng cao là lỗ hổng của bài này
+ Không giống như bài Phineas, ở bài này hàm `pickle_loads(s)` ta sẽ không thể gọi trực tiếp, mà nó chỉ được gọi khi render giao diện, được gọi trong file `index.html` và `item.html`

=> Sau khi phân tích source code thì mình có thể nhận định được rằng bài này phải RCE mới đọc được Flag (vì trong code không hề có chức năng nào đọc file flag) và khả năng cao đang bị dính lỗ hổng Insecure Deserialization, bây giờ ta sẽ phải tìm cách cho payload đi qua hàm `pickle_loads(s)`, từ đó có thể RCE
+ Ngoài Insecure Deserialization thì mình cũng nghĩ đến Blind SQLi vì database đang sử dụng Sqlite3 do task2 mình cũng đã làm và server phải có PHP. Nhưng bài này server đang chạy web bằng Python nên khả năng xảy ra lỗi này là không cao






