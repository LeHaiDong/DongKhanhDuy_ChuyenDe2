# Deploy MienTayShop len AWS Free Tier

Muc tieu: chay project Laravel tren 1 EC2 free-tier eligible, dung SQLite nam chung trong project. Cach nay khong dung RDS, Load Balancer, Elastic IP, S3 hay CDN de giam rui ro phat sinh phi.

## Nguyen tac tranh phi

- Tao Billing Budget truoc khi tao EC2.
- Chi tao 1 EC2 co nhan `Free tier eligible`.
- Dung Ubuntu 22.04 LTS.
- Storage EBS 20GB, khong tang qua 30GB.
- Khong tao RDS, Load Balancer, NAT Gateway, Elastic IP, S3, CloudFront.
- Security Group chi mo HTTP 80 va SSH 22. SSH nen chon `My IP`.
- Sau khi nop bai xong nen `Stop` hoac `Terminate` instance.

## Tao goi deploy tren may local

Chay trong PowerShell:

```powershell
cd C:\laragon\www\lar_vidu1
npm run build
php artisan optimize:clear
powershell -ExecutionPolicy Bypass -File C:\laragon\www\lar_vidu1\deploy\aws-free\build-package.ps1
```

File se duoc tao tai:

```text
C:\laragon\www\lar_vidu1\deploy\aws-free\mientayshop-aws-free.zip
```

## Buoc 1: Tao EC2 tren AWS Console

1. Vao AWS Console, chon region gan Viet Nam, vi du Singapore `ap-southeast-1`.
2. Vao EC2 -> Launch instance.
3. Name: `mientayshop-free`.
4. AMI: `Ubuntu Server 22.04 LTS`.
5. Instance type: chi chon loai co chu `Free tier eligible`, thuong la `t2.micro` hoac `t3.micro` tuy region.
6. Key pair: tao key moi, tai file `.pem` ve may.
7. Security Group:
   - SSH port 22: chon `My IP`.
   - HTTP port 80: chon `Anywhere`.
8. Storage: 20GB gp3 hoac mac dinh.
9. Launch instance.

## Buoc 2: Upload va cai dat tu Windows

Sau khi co Public IPv4 va file `.pem`, chay:

```powershell
powershell -ExecutionPolicy Bypass -File C:\laragon\www\lar_vidu1\deploy\aws-free\upload-and-deploy.ps1 -PemPath C:\path\to\your-key.pem -HostIp YOUR_EC2_PUBLIC_IP
```

Script se upload zip, bung code vao `/var/www/mientayshop`, cai Nginx/PHP/SQLite/Composer va cai cau hinh Nginx.

## Buoc 3: Sua APP_URL va deploy

SSH vao server:

```powershell
ssh -i C:\path\to\your-key.pem ubuntu@YOUR_EC2_PUBLIC_IP
```

Tren server:

```bash
cd /var/www/mientayshop
cp deploy/aws-free/.env.production.example .env
nano .env
```

Sua dong:

```env
APP_URL=http://YOUR_EC2_PUBLIC_IP
```

Deploy:

```bash
bash deploy/aws-free/deploy-on-server.sh
```

Mo trinh duyet:

```text
http://YOUR_EC2_PUBLIC_IP
```

## Neu can dung de tranh phi

Sau khi nop bai xong, vao EC2 Console:

- `Stop instance`: tam dung may chu.
- `Terminate instance`: xoa may chu.

Neu ban lo tao nham Elastic IP thi vao EC2 -> Elastic IPs va release no.
