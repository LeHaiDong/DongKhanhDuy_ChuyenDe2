<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('admin')->check()) {
            return redirect()
                ->route('admin.login')
                ->with('error', 'Vui lòng đăng nhập để truy cập khu vực admin.');
        }

        if (!Auth::guard('admin')->user()?->isAdmin()) {
            Auth::guard('admin')->logout();
            $request->session()->regenerate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->with('error', 'Bạn không có quyền truy cập khu vực admin.');
        }

        return $next($request);
    }
}
