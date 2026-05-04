@extends('layouts.app')

@section('title', 'Danh sach yeu thich - MienTayShop')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-gradient-to-br from-slate-950 via-slate-900 to-slate-800 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-500 to-red-500 mb-6">
                <i class="fas fa-heart text-2xl"></i>
            </div>
            <h1 class="text-4xl md:text-5xl font-black mb-4">Danh sach yeu thich</h1>
            <p class="text-lg text-slate-300 max-w-2xl mx-auto">
                Noi ban luu lai nhung san pham muon mua sau, tu cong nghe den tieu dung hang ngay.
            </p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if($favorites->count() > 0)
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h2 class="text-2xl font-black text-slate-900">{{ $favorites->total() }} san pham da luu</h2>
                    <p class="text-slate-500 mt-1">Tap hop cac mon hang ban dang can nhac.</p>
                </div>

                <form method="POST" action="{{ route('favorites.clear') }}" onsubmit="return confirm('Ban co chac muon xoa tat ca san pham yeu thich?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 text-white font-semibold hover:bg-red-600 transition">
                        <i class="fas fa-trash-alt mr-2"></i>
                        Xoa tat ca
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($favorites as $lens)
                    <div class="rounded-3xl overflow-hidden bg-white border border-slate-200 shadow-[0_20px_40px_rgba(15,23,42,0.06)]">
                        <a href="{{ route('products.show', $lens) }}" class="block relative">
                            @if($lens->image_url)
                                <img src="{{ $lens->image_url }}" alt="{{ $lens->name }}" class="w-full h-56 object-cover">
                            @else
                                <div class="w-full h-56 bg-gradient-to-br from-orange-50 to-slate-100 flex flex-col items-center justify-center gap-3 text-orange-600">
                                    <i class="{{ $lens->placeholder_icon }} text-5xl"></i>
                                    <span class="text-xs font-bold uppercase tracking-[0.22em]">{{ $lens->display_product_type }}</span>
                                </div>
                            @endif

                            <button type="button"
                                    class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/90 text-red-500 shadow-lg flex items-center justify-center"
                                    onclick="event.preventDefault(); toggleFavorite({{ $lens->id }})"
                                    data-lens-id="{{ $lens->id }}">
                                <i class="fas fa-heart"></i>
                            </button>
                        </a>

                        <div class="p-6">
                            <div class="flex items-center justify-between gap-3 mb-3 text-xs font-bold uppercase tracking-[0.18em]">
                                <span class="text-orange-600">{{ $lens->brand }}</span>
                                <span class="text-slate-400">{{ $lens->display_product_type }}</span>
                            </div>

                            <a href="{{ route('products.show', $lens) }}">
                                <h3 class="text-lg font-black text-slate-900 leading-7 mb-4 hover:text-orange-600 transition">
                                    {{ $lens->name }}
                                </h3>
                            </a>

                            <div class="grid gap-2 mb-4">
                                @foreach($lens->display_specifications->take(2) as $spec)
                                    <div class="flex justify-between gap-3 rounded-2xl bg-slate-50 px-3 py-2 text-sm">
                                        <span class="text-slate-500 font-semibold">{{ $spec['label'] }}</span>
                                        <span class="text-slate-800 font-bold text-right">{{ $spec['value'] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex items-center justify-between gap-3 mb-5">
                                <div class="text-2xl font-black text-slate-900">{{ number_format($lens->price, 0, ',', '.') }}₫</div>
                                <div class="text-sm {{ $lens->in_stock ? 'text-emerald-600' : 'text-red-500' }} font-semibold">
                                    {{ $lens->in_stock ? 'Con hang' : 'Het hang' }}
                                </div>
                            </div>

                            <div class="grid gap-3">
                                <a href="{{ route('products.show', $lens) }}" class="w-full text-center rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 text-white py-3 font-bold hover:from-orange-600 hover:to-orange-700 transition">
                                    <i class="fas fa-eye mr-2"></i>
                                    Xem chi tiet
                                </a>

                                @if($lens->in_stock)
                                    <button class="w-full rounded-2xl bg-slate-900 text-white py-3 font-bold hover:bg-slate-800 transition add-to-cart-btn" data-lens-id="{{ $lens->id }}">
                                        <i class="fas fa-shopping-cart mr-2"></i>
                                        Them vao gio
                                    </button>
                                @else
                                    <button disabled class="w-full rounded-2xl bg-slate-200 text-slate-500 py-3 font-bold cursor-not-allowed">
                                        <i class="fas fa-times mr-2"></i>
                                        Tam het hang
                                    </button>
                                @endif

                                <form method="POST" action="{{ route('favorites.destroy', $lens) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full rounded-2xl border border-red-200 bg-red-50 text-red-600 py-3 font-bold hover:bg-red-100 transition" onclick="return confirm('Bo san pham nay khoi danh sach yeu thich?')">
                                        <i class="fas fa-heart-broken mr-2"></i>
                                        Bo yeu thich
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($favorites->hasPages())
                <div class="mt-12 flex justify-center">
                    {{ $favorites->links() }}
                </div>
            @endif
        @else
            <div class="py-20 text-center">
                <div class="w-24 h-24 mx-auto rounded-full bg-white shadow flex items-center justify-center mb-6 text-slate-400">
                    <i class="fas fa-heart text-4xl"></i>
                </div>
                <h2 class="text-3xl font-black text-slate-900 mb-4">Chua co san pham nao duoc luu</h2>
                <p class="text-slate-500 max-w-xl mx-auto mb-8">
                    Thu them cac mon hang dang can nhac vao danh sach yeu thich de quay lai so sanh sau.
                </p>
                <a href="{{ route('products.shop') }}" class="inline-flex items-center px-6 py-3 rounded-2xl bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold hover:from-orange-600 hover:to-orange-700 transition">
                    <i class="fas fa-store mr-2"></i>
                    Kham pha san pham
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function toggleFavorite(lensId) {
    fetch(`/favorites/toggle/${lensId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && !data.is_favorited) {
            const card = document.querySelector(`[data-lens-id="${lensId}"]`)?.closest('.rounded-3xl');
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.96)';
                setTimeout(() => {
                    card.remove();
                    if (!document.querySelector('.rounded-3xl')) {
                        window.location.reload();
                    }
                }, 250);
            }
        }
    });
}

document.querySelectorAll('.add-to-cart-btn').forEach(button => {
    button.addEventListener('click', function() {
        const lensId = this.dataset.lensId;
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Dang them...';
        this.disabled = true;

        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                camera_lens_id: lensId,
                quantity: 1
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.innerHTML = '<i class="fas fa-check mr-2"></i>Da them';
            } else {
                this.innerHTML = originalText;
            }
        })
        .catch(() => {
            this.innerHTML = originalText;
        })
        .finally(() => {
            setTimeout(() => {
                this.disabled = false;
                this.innerHTML = originalText;
            }, 1200);
        });
    });
});
</script>
@endsection
