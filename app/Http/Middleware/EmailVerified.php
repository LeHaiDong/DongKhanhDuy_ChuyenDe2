<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EmailVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Nếu chưa đăng nhập, cho phép tiếp tục (middleware auth sẽ xử lý)
        if (!$user) {
            return $next($request);
        }

        // Nếu là admin, bỏ qua kiểm tra email verification
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Debug: Log thông tin user và email verification status
        Log::info('EmailVerified Middleware Check', [
            'user_id' => $user->id,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'has_verified_email' => $user->hasVerifiedEmail(),
            'is_admin' => $user->isAdmin(),
            'route' => $request->route()->getName(),
            'url' => $request->url()
        ]);

        // Nếu email chưa được xác thực, chuyển hướng đến trang xác thực
        if (!$user->hasVerifiedEmail()) {
            // Nếu đang ở trang verification, cho phép tiếp tục
            if ($request->routeIs('verification.*')) {
                return $next($request);
            }

            return redirect()->route('verification.notice')
                ->with('warning', 'Vui lòng xác thực email để tiếp tục sử dụng dịch vụ.');
        }

        return $next($request);
    }
}
