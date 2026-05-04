<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->filled('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $user = Auth::guard('admin')->user();

            if ($user && $user->isAdmin()) {
                $request->session()->regenerate();

                return redirect()
                    ->intended(route('admin.dashboard'))
                    ->with('success', 'Chào mừng trở lại, ' . $user->name . '!');
            }

            Auth::guard('admin')->logout();

            return back()->withErrors([
                'email' => 'Bạn không có quyền truy cập khu vực admin.',
            ])->withInput($request->except('password'));
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('admin.login')
            ->with('success', 'Đăng xuất thành công!');
    }

    public function checkAuth()
    {
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()?->isAdmin()) {
            $user = Auth::guard('admin')->user();

            return response()->json([
                'authenticated' => true,
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
        }

        return response()->json(['authenticated' => false]);
    }
}
