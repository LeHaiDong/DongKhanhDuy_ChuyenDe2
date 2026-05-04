<!-- Apple-style Product Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; margin-bottom: 60px;">
    @forelse($lenses as $lens)
        <div style="background: #fff; border-radius: 18px; overflow: hidden; transition: all 0.3s ease; cursor: pointer; box-shadow: 0 4px 20px rgba(0,0,0,0.08);"
             onclick="window.location.href='{{ route('products.show', $lens) }}'"
             onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 8px 40px rgba(0,0,0,0.12)'"
             onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.08)'">
            
            <!-- Product Image -->
            <div style="aspect-ratio: 1; background: #f5f5f7; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                <img src="{{ $lens->image_url }}"
                     alt="{{ $lens->name }}"
                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                     onmouseover="this.style.transform='scale(1.05)'"
                     onmouseout="this.style.transform='scale(1)'">
            </div>
            
            <!-- Product Info -->
            <div style="padding: 24px;">
                <!-- Product Name -->
                <h3 style="font-size: 21px; font-weight: 600; color: #1d1d1f; line-height: 1.19048; letter-spacing: -0.003em; margin-bottom: 8px; font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, sans-serif;">
                    {{ $lens->name }}
                </h3>
                
                <!-- Brand & Product Type -->
                <div style="display: flex; gap: 16px; margin-bottom: 12px;">
                    <span style="font-size: 14px; color: #86868b; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
                        {{ $lens->brand }}
                    </span>
                    <span style="font-size: 14px; color: #86868b; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
                        {{ $lens->display_product_type }}
                    </span>
                </div>
                
                <!-- Condition Badge -->
                @if($lens->condition)
                    <div style="margin-bottom: 16px;">
                        <span style="display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; 
                                     @if($lens->condition === 'new') background: #e7f5e7; color: #1b5e1f;
                                     @elseif($lens->condition === 'like_new') background: #e3f2fd; color: #0d47a1;
                                     @elseif($lens->condition === 'excellent') background: #fff3e0; color: #e65100;
                                     @elseif($lens->condition === 'good') background: #f3e5f5; color: #4a148c;
                                     @else background: #f5f5f7; color: #86868b; @endif
                                     font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
                            {{ ucfirst($lens->condition) }}
                        </span>
                    </div>
                @endif
                
                <!-- Price -->
                <div style="margin-bottom: 20px;">
                    <span style="font-size: 24px; font-weight: 600; color: #1d1d1f; font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, sans-serif;">
                        {{ number_format($lens->price, 0, ',', '.') }} VNĐ
                    </span>
                </div>
                
                <!-- Stock Status & Actions -->
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    @if($lens->stock_quantity > 0)
                        <span style="font-size: 14px; color: #1b5e1f; font-weight: 500; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
                            <i class="fas fa-check-circle" style="margin-right: 6px; color: #34c759;"></i>
                            Còn hàng ({{ $lens->stock_quantity }})
                        </span>
                        
                        <!-- Add to Cart Button -->
                        <button class="add-to-cart-btn" 
                                data-lens-id="{{ $lens->id }}"
                                style="background: #007aff; color: white; border: none; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; font-family: -apple-system, BlinkMacSystemFont, sans-serif;"
                                onmouseover="this.style.backgroundColor='#0051d5'"
                                onmouseout="this.style.backgroundColor='#007aff'"
                                onclick="event.stopPropagation()">
                            <i class="fas fa-plus" style="margin-right: 6px;"></i>
                            Thêm
                        </button>
                    @else
                        <span style="font-size: 14px; color: #86868b; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
                            <i class="fas fa-times-circle" style="margin-right: 6px;"></i>
                            Hết hàng
                        </span>
                        
                        <button disabled 
                                style="background: #f5f5f7; color: #86868b; border: none; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 500; cursor: not-allowed; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
                            Hết hàng
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <!-- Empty State -->
        <div style="grid-column: 1 / -1; text-align: center; padding: 80px 20px; background: #fff; border-radius: 18px;">
            <div style="font-size: 64px; color: #f5f5f7; margin-bottom: 24px;">
                <i class="fas fa-search"></i>
            </div>
            <h3 style="font-size: 24px; font-weight: 600; color: #1d1d1f; margin-bottom: 12px; font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, sans-serif;">
                Không tìm thấy sản phẩm
            </h3>
            <p style="font-size: 17px; color: #86868b; margin-bottom: 32px; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
                Hãy thử điều chỉnh bộ lọc hoặc từ khóa tìm kiếm
            </p>
            <a href="{{ route('products.shop') }}" 
               style="display: inline-block; background: #007aff; color: white; padding: 12px 24px; border-radius: 22px; text-decoration: none; font-size: 17px; font-weight: 500; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">
                Xem tất cả sản phẩm
            </a>
        </div>
    @endforelse
</div>

<!-- Pagination Apple-style -->
@if($lenses->hasPages())
    <div style="display: flex; justify-content: center; margin-top: 40px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            @if ($lenses->onFirstPage())
                <span style="padding: 8px 12px; color: #86868b; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">‹</span>
            @else
                <a href="{{ $lenses->previousPageUrl() }}" 
                   style="padding: 8px 12px; color: #007aff; text-decoration: none; border-radius: 8px; transition: background 0.2s; font-family: -apple-system, BlinkMacSystemFont, sans-serif;"
                   onmouseover="this.style.backgroundColor='#f5f5f7'"
                   onmouseout="this.style.backgroundColor='transparent'">‹</a>
            @endif

            @foreach ($lenses->getUrlRange(1, $lenses->lastPage()) as $page => $url)
                @if ($page == $lenses->currentPage())
                    <span style="padding: 8px 12px; background: #007aff; color: white; border-radius: 8px; font-weight: 500; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" 
                       style="padding: 8px 12px; color: #007aff; text-decoration: none; border-radius: 8px; transition: background 0.2s; font-family: -apple-system, BlinkMacSystemFont, sans-serif;"
                       onmouseover="this.style.backgroundColor='#f5f5f7'"
                       onmouseout="this.style.backgroundColor='transparent'">{{ $page }}</a>
                @endif
            @endforeach

            @if ($lenses->hasMorePages())
                <a href="{{ $lenses->nextPageUrl() }}" 
                   style="padding: 8px 12px; color: #007aff; text-decoration: none; border-radius: 8px; transition: background 0.2s; font-family: -apple-system, BlinkMacSystemFont, sans-serif;"
                   onmouseover="this.style.backgroundColor='#f5f5f7'"
                   onmouseout="this.style.backgroundColor='transparent'">›</a>
            @else
                <span style="padding: 8px 12px; color: #86868b; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">›</span>
            @endif
        </div>
    </div>
@endif


