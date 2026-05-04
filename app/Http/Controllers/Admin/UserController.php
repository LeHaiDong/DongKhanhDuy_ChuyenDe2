<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->where('is_admin', false);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $allowedSorts = ['name', 'email', 'created_at', 'updated_at'];
        $sortBy = in_array($request->get('sort'), $allowedSorts, true) ? $request->get('sort') : 'created_at';
        $sortOrder = $request->get('order') === 'asc' ? 'asc' : 'desc';

        $users = $query->orderBy($sortBy, $sortOrder)->paginate(15);

        $stats = [
            'total_customers' => User::where('is_admin', false)->count(),
            'new_this_week' => User::where('is_admin', false)
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'is_admin' => false,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Khách hàng đã được tạo thành công.');
    }

    public function show(User $user)
    {
        $this->abortIfNotCustomer($user);

        $stats = [
            'favorites_count' => $user->favorites()->count(),
            'chat_messages_count' => ChatMessage::where('user_id', $user->id)->count(),
            'join_date' => $user->created_at->format('d/m/Y'),
            'days_member' => $user->created_at->diffInDays(now()),
            'last_activity' => $user->updated_at->format('d/m/Y H:i'),
        ];

        $recentFavorites = $user->favoriteCameraLenses()
            ->latest('favorites.created_at')
            ->take(5)
            ->get();

        $recentMessages = ChatMessage::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.users.show', compact('user', 'stats', 'recentFavorites', 'recentMessages'));
    }

    public function edit(User $user)
    {
        $this->abortIfNotCustomer($user);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->abortIfNotCustomer($user);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'is_admin' => false,
        ];

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:8|confirmed',
            ]);

            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Thông tin khách hàng đã được cập nhật.');
    }

    public function destroy(User $user)
    {
        $this->abortIfNotCustomer($user);

        $user->favorites()->delete();
        ChatMessage::where('user_id', $user->id)->delete();
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Khách hàng đã được xóa thành công.');
    }

    public function getDashboardStats()
    {
        $totalUsers = User::count();
        $totalAdmins = User::where('is_admin', true)->count();
        $totalCustomers = User::where('is_admin', false)->count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $userGrowth = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $userGrowth[] = [
                'month' => $month->format('M Y'),
                'count' => User::whereMonth('created_at', $month->month)
                    ->whereYear('created_at', $month->year)
                    ->count(),
            ];
        }

        return [
            'total_users' => $totalUsers,
            'total_admins' => $totalAdmins,
            'total_customers' => $totalCustomers,
            'new_users_this_month' => $newUsersThisMonth,
            'user_growth' => $userGrowth,
        ];
    }

    public function toggleStatus(User $user)
    {
        return response()->json([
            'success' => false,
            'message' => 'Trang khách hàng không dùng để thay đổi quyền quản trị.',
        ], 403);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $userIds = User::whereIn('id', $request->user_ids)
            ->where('is_admin', false)
            ->pluck('id')
            ->all();

        if (empty($userIds)) {
            return redirect()->back()->with('error', 'Không có khách hàng hợp lệ để thao tác.');
        }

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $user->favorites()->delete();
                ChatMessage::where('user_id', $userId)->delete();
            }
        }

        User::whereIn('id', $userIds)->where('is_admin', false)->delete();

        return redirect()
            ->back()
            ->with('success', 'Đã xóa ' . count($userIds) . ' khách hàng thành công.');
    }

    private function abortIfNotCustomer(User $user): void
    {
        abort_if($user->is_admin, 404);
    }
}
