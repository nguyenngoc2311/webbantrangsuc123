# HƯỚNG DẪN CÀI ĐẶT VÀ CẤU HÌNH 
# WEBSITE BÁN TRANG SỨC

## 1. Tải và cài đặt XAMPP
Tải và cài đặt XAMPP phiên bản mới nhất (khuyến nghị 8.2 trở lên). Tải phần mềm tại https://www.apachefriends.org/download.html  
Chọn phiên bản phù hợp với hệ điều hành của bạn (Windows, Linux, macOS).

## 2. Sau khi cài đặt thành công, kiểm tra XAMPP
Vui lòng mở XAMPP Control Panel và khởi động Apache và MySQL.  
Kiểm tra bằng cách truy cập `http://localhost` trong trình duyệt. Nếu trang XAMPP Welcome xuất hiện là đã cài thành công.

## 3. Tải và cài đặt Visual Studio Code (Tùy chọn)
Tải và cài đặt Visual Studio Code để chỉnh sửa mã nguồn. Tải tại https://code.visualstudio.com/download

## 4. Tải và cài đặt Git
Tải và cài đặt Git để quản lý phiên bản mã nguồn. Tải tại https://git-scm.com/downloads  
Sau khi cài đặt, kiểm tra bằng lệnh: `git --version` trong CMD.

## 5. Tải và cài đặt MySQL Workbench (Tùy chọn)
Tải và cài đặt MySQL Workbench bản community để quản lý cơ sở dữ liệu. Tải tại https://dev.mysql.com/downloads/workbench/

## 6. Clone dự án từ GitHub
Mở CMD hoặc Terminal, điều hướng đến thư mục `C:\xampp\htdocs` và chạy lệnh:  
`git clone https://github.com/yourusername/webbantrangsuc.git`  
(Thay `yourusername` bằng tên người dùng GitHub của bạn. Nếu chưa có repo, tạo repo trên GitHub và push code lên trước.)  
Sau khi clone, thư mục dự án sẽ là `C:\xampp\htdocs\webbantrangsuc`.

## 7. Thiết lập Cơ Sở Dữ Liệu
Mở phpMyAdmin bằng cách truy cập `http://localhost/phpmyadmin` trong trình duyệt.  
Tạo một cơ sở dữ liệu mới có tên `trangsuc_db1`.  
Import file `database_schema.sql` (đã được tạo sẵn trong thư mục dự án):  
- Chọn cơ sở dữ liệu `trangsuc_db1`.  
- Chọn tab "Import".  
- Chọn file `database_schema.sql` và nhấn "Go".

## 8. Cấu hình kết nối Cơ Sở Dữ Liệu
Mở tệp `php/config.php`.  
Đảm bảo các thông tin kết nối chính xác:  
- `$servername = "localhost";`  
- `$username = "root";`  
- `$password = "";` (mặc định không có mật khẩu)  
- `$dbname = "trangsuc_db1";`

## 9. Cấu hình VNPay (Tùy chọn cho Thanh toán)
Mở tệp `vnpay_php/config.php`.  
Cập nhật các thông tin VNPay nếu cần:  
- `$vnp_TmnCode`: Mã định danh thương nhân  
- `$vnp_HashSecret`: Chuỗi bí mật  
- `$vnp_Url`: URL thanh toán  
- `$vnp_Returnurl`: URL trả về  

Lưu ý: Sử dụng sandbox cho môi trường thử nghiệm.

## 10. Chạy dự án
Mở trình duyệt và truy cập `http://localhost/webbantrangsuc/php/index.php` hoặc `http://localhost/webbantrangsuc/` nếu có tệp index.php ở root.  
Đăng ký tài khoản người dùng hoặc đăng nhập với tài khoản admin để quản lý.

## 11. Thêm Dữ Liệu Mẫu (Tùy chọn)
- Sử dụng giao diện admin để thêm sản phẩm, danh mục, thương hiệu, v.v.  
- Hoặc chèn dữ liệu mẫu trực tiếp vào cơ sở dữ liệu qua phpMyAdmin.  
- Bạn có thể chỉnh sửa file `database_schema.sql` để thêm các INSERT statements mẫu nếu cần.

## Lưu Ý
- Đảm bảo PHP có hỗ trợ mysqli.  
- Nếu gặp lỗi, kiểm tra log lỗi của Apache và MySQL trong XAMPP.  
- Dự án sử dụng UTF-8 cho ký tự tiếng Việt.  
- Nếu cần hỗ trợ, kiểm tra mã nguồn và đảm bảo tất cả các tệp được sao chép đúng cách.

## Hướng Dẫn Push Code Lên GitHub (Nếu Chưa Có Repo)
1. Tạo repository mới trên GitHub (https://github.com/new).  
2. Trong thư mục dự án, chạy:  
   `git init`  
   `git add .`  
   `git commit -m "Initial commit"`  
   `git branch -M main`  
   `git remote add origin https://github.com/yourusername/webbantrangsuc.git`  
   `git push -u origin main`  
3. Thay `yourusername` bằng tên người dùng GitHub của bạn.