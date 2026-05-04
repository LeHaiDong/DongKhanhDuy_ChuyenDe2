# Hướng dẫn cấu hình Email Verification

## 🚀 Tổng quan
Hệ thống xác thực email đã được tích hợp hoàn chỉnh với các tính năng:
- Gửi mã OTP 6 số qua email sau khi đăng ký
- Xác thực email bằng mã OTP
- Middleware bảo vệ các chức năng yêu cầu email đã xác thực
- Gửi lại mã xác thực
- Mã có thời hạn 15 phút

## 📧 Cấu hình Email Service

### Option 1: Sử dụng Mailtrap (Recommended for Development)
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@lensstore.com"
MAIL_FROM_NAME="Lens Store"
```

### Option 2: Sử dụng Gmail SMTP
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_gmail@gmail.com"
MAIL_FROM_NAME="Lens Store"
```

**Lưu ý cho Gmail:**
- Cần bật 2-Factor Authentication
- Tạo App Password thay vì dùng password thường
- Truy cập: Google Account > Security > App passwords

### Option 3: Sử dụng Mailpit (Local Development)
```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@lensstore.com"
MAIL_FROM_NAME="Lens Store"
```

## 🗄️ Chạy Migration

```bash
# Khởi động XAMPP MySQL trước
php artisan migrate
```

Migration sẽ thêm các trường:
- `email_verification_code` (string, 6 ký tự)
- `verification_code_expires_at` (timestamp)

## 🔧 Cấu hình Queue (Tùy chọn)

Để gửi email nhanh hơn, có thể sử dụng queue:

```env
QUEUE_CONNECTION=database
```

Chạy migration cho queue:
```bash
php artisan queue:table
php artisan migrate
```

Chạy queue worker:
```bash
php artisan queue:work
```

## 🛣️ Routes đã được thêm

```php
// Email Verification routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'show'])->name('verification.notice');
    Route::post('/email/verify', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');
});
```

## 🛡️ Middleware đã được áp dụng

Middleware `email.verified` đã được áp dụng cho:
- Profile routes (thông tin cá nhân, đổi mật khẩu)
- **KHÔNG áp dụng cho Cart và Favorites** - chỉ cần đăng nhập

**Lưu ý quan trọng**: Cart và Favorites chỉ yêu cầu đăng nhập (`auth` middleware), không yêu cầu email verification để cải thiện trải nghiệm người dùng.

## 🎯 Luồng hoạt động

1. **Đăng ký** → Tạo user → Gửi email OTP → Chuyển đến trang xác thực
2. **Đăng nhập** → Kiểm tra email verified → Nếu chưa → Chuyển đến trang xác thực
3. **Xác thực** → Nhập mã OTP → Verify thành công → Có thể sử dụng đầy đủ tính năng
4. **Gửi lại** → Tạo mã mới → Gửi email mới

## 🧪 Test chức năng

1. Đăng ký tài khoản mới
2. Kiểm tra email nhận được mã OTP
3. Nhập mã để xác thực
4. Thử truy cập các chức năng yêu cầu email verified

## 🔍 Troubleshooting

### Email không được gửi
- Kiểm tra cấu hình SMTP trong `.env`
- Kiểm tra log: `storage/logs/laravel.log`
- Test kết nối SMTP

### Mã OTP không đúng
- Kiểm tra thời gian hết hạn (15 phút)
- Đảm bảo nhập đúng 6 chữ số
- Thử gửi lại mã mới

### Middleware không hoạt động
- Kiểm tra middleware đã được đăng ký trong `Kernel.php`
- Kiểm tra routes có áp dụng middleware `email.verified`

## 📝 Customization

### Thay đổi thời gian hết hạn mã
Trong `User.php`:
```php
'verification_code_expires_at' => now()->addMinutes(30), // 30 phút
```

### Thay đổi độ dài mã OTP
Trong `User.php`:
```php
$code = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT); // 4 số
```

### Tùy chỉnh email template
Chỉnh sửa file: `resources/views/emails/email-verification.blade.php`

### Thêm middleware cho routes khác
```php
Route::get('/profile', [Controller::class, 'profile'])->middleware(['auth', 'email.verified']);
```

## 🎨 Giao diện

- **Trang xác thực**: `/email/verify`
- **Responsive design** với Tailwind CSS
- **Auto-format** input mã OTP
- **Real-time validation**
- **Animations** và effects

## 🔐 Bảo mật

- Mã OTP có thời hạn 15 phút
- Chỉ áp dụng cho customer (không áp dụng cho admin)
- Mã được hash và lưu trong database
- Validation nghiêm ngặt input

## 📱 Mobile Friendly

- Responsive design
- Touch-friendly buttons
- Auto-focus input
- Optimized for mobile keyboards
