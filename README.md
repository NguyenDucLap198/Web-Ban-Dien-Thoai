# WebPhoneS

Website cửa hàng công nghệ viết bằng PHP thuần và MySQL, chạy trên XAMPP. Có trang chủ, danh mục sản phẩm công nghệ, tìm kiếm và sắp xếp sản phẩm, chi tiết sản phẩm, giỏ hàng, đặt hàng, liên hệ; khách hàng có thể đăng ký, đăng nhập và xem lịch sử đơn hàng. Trang quản trị hỗ trợ thêm/sửa/xóa sản phẩm, xem chi tiết/cập nhật trạng thái đơn hàng, quản lý tin nhắn liên hệ, đổi mật khẩu và tùy chỉnh thông báo cửa hàng.

Yêu cầu PHP 8.0 trở lên và MySQL/MariaDB.

## Cài đặt bằng XAMPP

1. Đặt thư mục `WebPhoneS` trong `C:\xampp\htdocs`.
2. Mở XAMPP Control Panel, khởi động **Apache** và **MySQL**.
3. Truy cập `http://localhost/phpmyadmin`, chọn **Import**, chọn file `database.sql` trong thư mục dự án rồi nhấn **Import**.
4. Mở `http://localhost/WebPhoneS`.
5. Trang quản trị: `http://localhost/WebPhoneS/admin/login.php`. Tài khoản mẫu: **admin** / **admin123**. Đăng nhập rồi mở **Đổi mật khẩu** trước khi đưa website lên Internet.

Khách hàng đăng ký tại `http://localhost/WebPhoneS/register.php`, sau đó đăng nhập để xem lịch sử đơn hàng. Đơn hàng mới được tạo khi đăng nhập sẽ gắn với tài khoản khách hàng; khách vẫn có thể đặt hàng không cần tài khoản.

Trang **Giao diện cửa hàng** trong quản trị cho phép cập nhật hoặc ẩn thông báo chạy ngang. Khi nâng cấp cơ sở dữ liệu hiện có, chạy lại file `database.sql` qua phpMyAdmin để thêm cột danh mục và bảng cấu hình thông báo; dữ liệu sản phẩm và đơn hàng hiện tại được giữ nguyên.

## Cấu hình cơ sở dữ liệu

Mặc định ứng dụng dùng database `webphones`, MySQL host `127.0.0.1`, user `root`, mật khẩu trống (thiết lập mặc định thường gặp của XAMPP). Có thể ghi đè bằng biến môi trường `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`.

Ứng dụng sử dụng PDO, truy vấn tham số hóa, mã CSRF cho các thao tác ghi dữ liệu và giao dịch database khi tạo đơn hàng. Ảnh sản phẩm mẫu được tải từ Unsplash; có thể thay URL trong quản trị sản phẩm.

File SQL dùng UTF-8/`utf8mb4`. Nếu nhập bằng MySQL command line thay vì phpMyAdmin, hãy bật bộ ký tự này cho client (ví dụ `mysql --default-character-set=utf8mb4 -u root < database.sql`) để tiếng Việt được lưu chính xác. Khi nhập lại, script cũng khôi phục mô tả mẫu nếu trước đó đã bị hỏng thành dấu `?`.
