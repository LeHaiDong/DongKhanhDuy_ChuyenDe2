<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationMail;

class EmailVerificationController extends Controller
{
    /**
     * Show email verification form
     */
    public function show()
    {
        $user = Auth::user();

        // Nếu đã xác thực email, chuyển hướng về trang chủ
        if ($user && $user->hasVerifiedEmail()) {
            return redirect()->route('home')->with('success', 'Email đã được xác thực!');
        }

        return view('auth.verify-email');
    }

    /**
     * Verify email with code
     */
    public function verify(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if (!$user) {
            return redirect()->route('auth.customer.login')
                ->with('error', 'Vui lòng đăng nhập để xác thực email.');
        }

        // Kiểm tra mã xác thực
        if ($user->verifyEmail($request->verification_code)) {
            return redirect()->route('home')
                ->with('success', 'Email đã được xác thực thành công! Chào mừng bạn đến với Lens Store!');
        }

        // Kiểm tra mã hết hạn
        if ($user->isVerificationCodeExpired()) {
            return back()->withErrors([
                'verification_code' => 'Mã xác thực đã hết hạn. Vui lòng yêu cầu mã mới.',
            ]);
        }

        return back()->withErrors([
            'verification_code' => 'Mã xác thực không chính xác.',
        ]);
    }

    /**
     * Resend verification email
     */
    public function resend(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('auth.customer.login')
                ->with('error', 'Vui lòng đăng nhập để gửi lại mã xác thực.');
        }

        // Nếu đã xác thực email
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('home')
                ->with('success', 'Email đã được xác thực!');
        }

        // Tạo mã xác thực mới
        $verificationCode = $user->generateEmailVerificationCode();

        // Gửi email
        try {
            Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationCode));

            return back()->with('success', 'Mã xác thực mới đã được gửi đến email của bạn!');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi gửi email. Vui lòng thử lại sau.');
        }
    }
}
