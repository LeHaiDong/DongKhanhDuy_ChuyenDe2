<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xac thuc email - MienTayShop</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #334155;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }

        .email-container {
            background: white;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 84px;
            height: 84px;
            background: linear-gradient(135deg, #0f172a, #2563eb);
            border-radius: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .logo svg {
            width: 42px;
            height: 42px;
            color: white;
        }

        h1 {
            color: #0f172a;
            margin: 0 0 10px 0;
            font-size: 28px;
            font-weight: 800;
        }

        .subtitle {
            color: #64748b;
            font-size: 16px;
            margin: 0;
        }

        .content {
            margin: 30px 0;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #0f172a;
        }

        .verification-code {
            background: linear-gradient(135deg, #0f172a, #2563eb);
            color: white;
            font-size: 32px;
            font-weight: 800;
            text-align: center;
            padding: 20px;
            border-radius: 14px;
            letter-spacing: 8px;
            margin: 30px 0;
            font-family: 'Courier New', monospace;
        }

        .instructions {
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #2563eb;
            margin: 20px 0;
        }

        .warning {
            background: #eff6ff;
            color: #1e40af;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #bfdbfe;
            margin: 20px 0;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .email-container {
                padding: 20px;
            }

            .verification-code {
                font-size: 24px;
                letter-spacing: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14l-1 11H6L5 8z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8V6a3 3 0 016 0v2" />
                </svg>
            </div>
            <h1>Xac thuc email</h1>
            <p class="subtitle">Hoan tat dang ky tai khoan MienTayShop</p>
        </div>

        <div class="content">
            <p class="greeting">Xin chao <strong>{{ $user->name }}</strong>,</p>

            <p>Cam on ban da tao tai khoan tai <strong>MienTayShop</strong>. De kich hoat tai khoan va tiep tuc mua sam, vui long su dung ma xac thuc duoi day:</p>

            <div class="verification-code">
                {{ $verificationCode }}
            </div>

            <div class="instructions">
                <h3 style="margin-top: 0; color: #0f172a;">Huong dan xac thuc:</h3>
                <ol style="margin: 0; padding-left: 20px;">
                    <li>Mo trang xac thuc email tren website</li>
                    <li>Nhap ma xac thuc <strong>{{ $verificationCode }}</strong></li>
                    <li>Bam nut xac thuc de hoan tat</li>
                </ol>
            </div>

            <div class="warning">
                <strong>Luu y:</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Ma xac thuc co hieu luc trong <strong>15 phut</strong></li>
                    <li>Khong chia se ma nay voi bat ky ai</li>
                    <li>Neu ban khong thuc hien dang ky nay, hay bo qua email</li>
                </ul>
            </div>

            <p>Sau khi xac thuc thanh cong, ban co the:</p>
            <ul>
                <li>Mua sam trong catalog da mat hang</li>
                <li>Luu san pham yeu thich</li>
                <li>Su dung chatbot de hoi nhanh ve san pham</li>
                <li>Theo doi thong tin tai khoan va don hang</li>
            </ul>
        </div>

        <div class="footer">
            <p>Email nay duoc gui tu <strong>MienTayShop</strong>.</p>
            <p>Day la email tu dong cua MienTayShop, vui long khong tra loi truc tiep.</p>
            <p style="font-size: 12px; color: #94a3b8;">
                © {{ date('Y') }} MienTayShop. Cua hang da nganh.
            </p>
        </div>
    </div>
</body>
</html>
