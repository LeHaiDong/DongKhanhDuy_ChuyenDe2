<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class CustomerAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.customer.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'is_admin' => false,
        ];

        $remember = $request->boolean('remember');
        $previousSessionId = $request->session()->getId();

        if (!Auth::guard('web')->attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'Thông tin đăng nhập không chính xác.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::guard('web')->user();
        $sessionCartItems = Cart::with('cameraLens')
            ->where('session_id', $previousSessionId)
            ->get();
        $sellerShopId = $user->sellerShop?->id;

        foreach ($sessionCartItems as $sessionItem) {
            if ($sellerShopId && (int) $sessionItem->cameraLens?->seller_shop_id === (int) $sellerShopId) {
                $sessionItem->delete();
                continue;
            }

            if ($sessionItem->is_direct_checkout) {
                $sessionItem->update([
                    'user_id' => $user->id,
                    'session_id' => null,
                ]);

                continue;
            }

            $existingItem = Cart::where('user_id', $user->id)
                ->where('camera_lens_id', $sessionItem->camera_lens_id)
                ->where('is_direct_checkout', false)
                ->first();

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => min(10, $existingItem->quantity + $sessionItem->quantity),
                    'unit_price' => $sessionItem->unit_price,
                ]);

                $sessionItem->delete();
                continue;
            }

            $sessionItem->update([
                'user_id' => $user->id,
                'session_id' => null,
            ]);
        }

        return redirect()
            ->intended(route('home'))
            ->with('success', 'Đăng nhập thành công!');
    }

    public function showRegisterForm()
    {
        return view('auth.customer.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_admin' => false,
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'verification_code_expires_at' => null,
        ])->save();

        return redirect()
            ->route('auth.customer.login')
            ->with('success', 'Đăng ký thành công! Bạn có thể đăng nhập ngay.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect('/')
            ->with('success', 'Đăng xuất thành công!');
    }

    public function profile()
    {
        $orders = Auth::guard('web')->user()
            ->orders()
            ->with('items.cameraLens')
            ->take(5)
            ->get();

        return view('auth.customer.profile', compact('orders'));
    }

    public function orders()
    {
        $orders = Auth::guard('web')->user()
            ->orders()
            ->with('items.cameraLens')
            ->latest()
            ->paginate(8);

        return view('auth.customer.orders', compact('orders'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('web')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function showChangePasswordForm()
    {
        return view('auth.customer.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = Auth::guard('web')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Mật khẩu hiện tại không chính xác.',
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }
}
