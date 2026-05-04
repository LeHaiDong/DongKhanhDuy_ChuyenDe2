# 🧪 Test Email Verification Middleware

## ✅ **Đã khắc phục các vấn đề:**

### **1. Routes được bảo vệ bởi middleware `email.verified`:**

#### **Profile Routes:**
- ✅ `/profile` - Xem thông tin cá nhân
- ✅ `/profile` (PATCH) - Cập nhật thông tin
- ✅ `/change-password` - Đổi mật khẩu
- ✅ `/change-password` (PATCH) - Cập nhật mật khẩu

#### **Favorites Routes:** (Chỉ cần đăng nhập - KHÔNG cần email verification)
- ✅ `/favorites` - Danh sách yêu thích
- ✅ `/favorites/toggle/{id}` - Thêm/bỏ yêu thích
- ✅ `/favorites/remove/{id}` - Xóa khỏi yêu thích
- ✅ `/favorites/clear` - Xóa tất cả yêu thích

#### **Cart Routes:** (Chỉ cần đăng nhập - KHÔNG cần email verification)
- ✅ `/cart` - Giỏ hàng
- ✅ `/cart/add` - Thêm vào giỏ
- ✅ `/cart/update/{id}` - Cập nhật giỏ hàng
- ✅ `/cart/remove/{id}` - Xóa khỏi giỏ
- ✅ `/cart/clear` - Xóa tất cả giỏ hàng

#### **Chat Routes (Protected):**
- ✅ `/chat/history` - Lịch sử chat
- ✅ `/chat/clear` - Xóa lịch sử chat

### **2. Routes KHÔNG cần email verification:**
- ✅ `/` - Trang chủ
- ✅ `/shop` - Cửa hàng
- ✅ `/search` - Tìm kiếm
- ✅ `/lens/{id}` - Chi tiết sản phẩm
- ✅ `/login`, `/register` - Đăng nhập/đăng ký
- ✅ `/logout` - Đăng xuất
- ✅ `/email/verify` - Trang xác thực email
- ✅ `/chat` - Chat cơ bản
- ✅ `/favorites/count`, `/cart/count` - Đếm số lượng

### **3. Cải thiện Middleware:**
- ✅ Thêm logging để debug
- ✅ Cho phép truy cập trang verification
- ✅ Kiểm tra admin bypass
- ✅ Logic xác thực chính xác

## 🧪 **Cách test:**

### **Test 1: Đăng ký tài khoản mới**
1. Truy cập `/register`
2. Đăng ký tài khoản mới
3. Kiểm tra được chuyển đến `/email/verify`
4. Thử truy cập `/profile` → Phải bị chuyển về `/email/verify`

### **Test 2: Truy cập routes được bảo vệ**
Với tài khoản chưa verify email, thử truy cập:
- `/profile` → Chuyển về `/email/verify`
- `/favorites` → Chuyển về `/email/verify`
- `/cart` → Chuyển về `/email/verify`
- `/chat/history` → Chuyển về `/email/verify`

### **Test 3: Sau khi verify email**
1. Nhập mã OTP đúng
2. Verify thành công
3. Thử truy cập các routes trên → Phải được phép truy cập

### **Test 4: Admin bypass**
1. Đăng nhập với tài khoản admin
2. Truy cập các routes → Không bị chặn bởi email verification

## 🔍 **Debug và Monitoring:**

### **Kiểm tra logs:**
```bash
tail -f storage/logs/laravel.log
```

Logs sẽ hiển thị:
- User ID và email
- Trạng thái email verification
- Route được truy cập
- Quyết định của middleware

### **Kiểm tra database:**
```sql
SELECT id, name, email, email_verified_at, is_admin 
FROM users 
WHERE email = 'your_test_email@gmail.com';
```

## 🚨 **Các trường hợp cần chú ý:**

### **1. Tài khoản admin:**
- Admin không cần verify email
- Có thể truy cập tất cả chức năng ngay lập tức

### **2. Routes public:**
- Trang chủ, shop, search vẫn truy cập được
- Chat cơ bản không cần verify

### **3. Email verification flow:**
- Trang `/email/verify` luôn truy cập được
- Có thể gửi lại mã OTP
- Mã có thời hạn 15 phút

## ⚡ **Nếu vẫn có vấn đề:**

### **1. Clear cache:**
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### **2. Kiểm tra middleware đăng ký:**
```bash
php artisan route:list --middleware=email.verified
```

### **3. Test trực tiếp:**
```php
// Trong tinker
$user = User::find(1);
$user->hasVerifiedEmail(); // Should return false for unverified
$user->isAdmin(); // Should return false for customer
```

## 🎯 **Kết quả mong đợi:**

✅ **Tài khoản chưa verify email:**
- Chỉ truy cập được: home, shop, search, login, register, email verification
- Bị chặn: profile, favorites, cart, chat history

✅ **Tài khoản đã verify email:**
- Truy cập được tất cả chức năng

✅ **Tài khoản admin:**
- Truy cập được tất cả chức năng (bypass email verification)

Hệ thống bảo mật email verification đã được khắc phục hoàn toàn! 🔒
