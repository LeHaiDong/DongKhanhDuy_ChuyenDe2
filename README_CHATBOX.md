# 🤖 HƯỚNG DẪN CHẠY CHATBOX

## 🚀 Cách Chạy Nhanh (Khuyến nghị)

### 1. **Chạy Tất Cả (1 Click)**
```bash
# Double-click file này hoặc chạy trong terminal:
start_chatbox.bat
```
- ✅ Tự động kiểm tra MySQL
- ✅ Khởi chạy Python service trong terminal riêng
- ✅ Khởi chạy Laravel serve 
- ✅ Hiển thị thông tin website

### 2. **Chạy Riêng Python Service**
```bash
start_python_only.bat
```

### 3. **Dừng Tất Cả Services** 
```bash
stop_services.bat
```

---

## 🌐 Truy Cập Website

- **Website chính**: http://localhost:8000
- **Admin panel**: http://localhost:8000/admin  
- **Chatbox**: Góc phải màn hình (nút chat màu xanh)

---

## 🔧 Cách Chạy Thủ Công

### Terminal 1: Python Service
```bash
python\chat_service\.venv\Scripts\python.exe -m uvicorn python.chat_service.main:app --host 127.0.0.1 --port 8005
```

### Terminal 2: Laravel Server
```bash
php artisan serve
```

---

## 🧪 Test Chatbox

### Các Câu Test:
1. **"Shop có khuyến mãi không?"** → Keyword reply
2. **"Tôi dùng Sony chụp chân dung"** → AI nhớ preferences  
3. **"SĐT 0903123456"** → Thu thập lead
4. **"Email: test@gmail.com"** → Thu thập lead
5. **"Chuyển nhân viên tư vấn"** → Handoff request

### Kiểm Tra Backend:
- Database `chat_sessions` - thống kê phiên chat
- Database `chat_events` - ghi events (intent, keyword, handoff)
- Database `leads` - thông tin khách hàng thu thập được

---

## ⚙️ Cấu Hình .env

Đảm bảo có các dòng này trong `.env`:
```env
PY_CHAT_ENABLED=true
PY_CHAT_URL=http://127.0.0.1:8005
OPENROUTER_API_KEY=your_key_here
```

---

## 🐛 Xử Lý Lỗi

### Port đã được sử dụng:
```bash
stop_services.bat
```

### Python service không chạy:
```bash
start_python_only.bat
```

### MySQL chưa chạy:
- Mở XAMPP Control Panel
- Start Apache & MySQL

---

## 📊 Monitoring

### Kiểm tra services:
```bash
# Kiểm tra Python service
curl http://127.0.0.1:8005/health

# Kiểm tra Laravel
curl http://localhost:8000
```

### Xem logs:
- Python logs hiển thị trong terminal Python
- Laravel logs: `storage/logs/laravel.log`
