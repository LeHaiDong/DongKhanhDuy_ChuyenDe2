# DongKhanhDuy_ChuyenDe2

Đề tài: Xây dựng nền tảng bán hàng trực tuyến.

MienTayShop là website thương mại điện tử đa ngành được xây dựng bằng Laravel. Hệ thống có trang khách hàng, giỏ hàng, đặt hàng, lịch sử mua, voucher, chatbot hỗ trợ, kênh người bán và trang quản trị hệ thống.

## Chức năng chính

- Khách hàng có thể xem danh mục, tìm kiếm sản phẩm, thêm vào giỏ hàng, mua ngay và theo dõi lịch sử mua hàng.
- Người bán đăng ký kênh bán, chờ admin duyệt, sau đó quản lý sản phẩm và xử lý đơn hàng của shop mình.
- Admin quản lý danh mục, khách hàng, người bán và chương trình voucher.
- Chatbot hỗ trợ tư vấn sản phẩm, cách đặt hàng, thanh toán, voucher và các câu hỏi phổ biến.
- Giao diện có hình ảnh sản phẩm, danh mục, flash sale, voucher và thông báo theo từng tài khoản.

## Công nghệ sử dụng

- PHP 8.x
- Laravel 9.x
- SQLite/MySQL
- Blade Template
- Tailwind/Vite
- FontAwesome

## Cách chạy local

```powershell
cd C:\laragon\www\DongKhanhDuy_ChuyenDe2
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8001
```

Mở trình duyệt tại:

```text
http://127.0.0.1:8001
```

## Tài khoản quản trị

Sau khi chạy seed, đăng nhập trang quản trị tại:

```text
http://127.0.0.1:8001/admin/login
```

Tài khoản mặc định:

```text
Email: admin@mientayshop.com
Mật khẩu: admin123
```

## Ghi chú khi nộp bài

- Không đẩy file `.env`, `vendor`, `node_modules`, database SQLite và các file zip deploy lên GitHub.
- Nếu ảnh chưa hiện sau khi đổi máy, chạy lại `php artisan storage:link` và `php artisan optimize:clear`.
- Dữ liệu mẫu được tạo bằng migration và seeder để người chấm có thể chạy lại project sạch.
