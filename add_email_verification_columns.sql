-- SQL để thêm các cột email verification vào bảng users
-- Chạy trong phpMyAdmin hoặc MySQL command line

USE ecommerce2024;

-- Thêm cột email_verification_code
ALTER TABLE users 
ADD COLUMN email_verification_code VARCHAR(6) NULL 
AFTER email;

-- Thêm cột verification_code_expires_at  
ALTER TABLE users 
ADD COLUMN verification_code_expires_at TIMESTAMP NULL 
AFTER email_verification_code;

-- Kiểm tra cấu trúc bảng
DESCRIBE users;
