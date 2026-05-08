<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                match ($request->status) {
                    'active' => $query->where('is_active', true)
                        ->where('starts_at', '<=', now())
                        ->where('expires_at', '>=', now())
                        ->where(function ($q) {
                            $q->whereNull('usage_limit')
                                ->orWhereRaw('used_count < usage_limit');
                        }),
                    'inactive' => $query->where('is_active', false),
                    'expired' => $query->where('expires_at', '<', now()),
                    'scheduled' => $query->where('starts_at', '>', now()),
                    default => null,
                };
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Coupon::count(),
            'active' => Coupon::available()->count(),
            'used' => Coupon::sum('used_count'),
            'expired' => Coupon::where('expires_at', '<', now())->count(),
        ];

        return view('admin.coupons.index', compact('coupons', 'stats'));
    }

    public function create()
    {
        $coupon = new Coupon([
            'type' => Coupon::TYPE_FIXED,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
            'usage_limit_per_user' => 1,
        ]);

        return view('admin.coupons.create', compact('coupon'));
    }

    public function store(Request $request)
    {
        Coupon::create($this->validatedData($request));

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Đã tạo chương trình voucher mới.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($this->validatedData($request, $coupon));

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Đã cập nhật chương trình voucher.');
    }

    public function destroy(Coupon $coupon)
    {
        if ($coupon->used_count > 0) {
            return back()->with('error', 'Voucher đã có lượt sử dụng nên không nên xóa. Bạn có thể tạm dừng voucher này.');
        }

        $coupon->delete();

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Đã xóa voucher.');
    }

    public function toggleStatus(Coupon $coupon)
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('success', $coupon->is_active ? 'Voucher đã được bật.' : 'Voucher đã được tạm dừng.');
    }

    private function validatedData(Request $request, ?Coupon $coupon = null): array
    {
        $this->normalizeNumberInputs($request);

        $rules = [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('coupons', 'code')->ignore($coupon?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', Rule::in([Coupon::TYPE_FIXED, Coupon::TYPE_PERCENTAGE])],
            'value' => ['required', 'numeric', 'min:1'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_user' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after:starts_at'],
            'first_order_only' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($request->input('type') === Coupon::TYPE_PERCENTAGE) {
            $rules['value'][] = 'max:100';
        }

        $data = $request->validate($rules, [
            'value.max' => 'Voucher giảm theo phần trăm chỉ được nhập từ 1 đến 100. Ví dụ: nhập 10 để giảm 10%.',
            'value.min' => 'Giá trị giảm phải lớn hơn hoặc bằng 1.',
            'value.numeric' => 'Giá trị giảm chỉ được nhập số.',
            'minimum_amount.numeric' => 'Đơn tối thiểu chỉ được nhập số.',
            'maximum_discount.numeric' => 'Giảm tối đa chỉ được nhập số.',
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['first_order_only'] = $request->boolean('first_order_only');
        $data['is_active'] = $request->boolean('is_active');

        if ($data['type'] === Coupon::TYPE_FIXED) {
            $data['maximum_discount'] = null;
        }

        return $data;
    }

    private function normalizeNumberInputs(Request $request): void
    {
        foreach (['value', 'minimum_amount', 'maximum_discount'] as $field) {
            if ($request->has($field)) {
                $request->merge([
                    $field => $this->normalizeNumberInput($request->input($field)),
                ]);
            }
        }
    }

    private function normalizeNumberInput($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(' ', '', $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, ',')) {
            $value = str_replace(',', '.', $value);
        } elseif (str_contains($value, '.')) {
            $parts = explode('.', $value);

            if (count($parts) > 2 || (count($parts) === 2 && strlen(end($parts)) === 3 && strlen($parts[0]) <= 3)) {
                $value = str_replace('.', '', $value);
            }
        }

        return $value;
    }
}
