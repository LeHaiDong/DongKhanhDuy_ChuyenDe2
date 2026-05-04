@extends('layouts.admin')

@section('title', 'Chỉnh sửa danh mục - Admin Panel')
@section('page-title', 'Chỉnh sửa danh mục')

@section('content')
<div class="admin-card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-edit" style="margin-right: 8px; color: #ffc107;"></i>
            Chỉnh sửa danh mục: {{ $category->display_name }}
        </h3>
        <div class="card-actions">
            <a href="{{ route('admin.categories.show', $category) }}" class="nike-btn nike-btn-outline">
                <i class="fas fa-eye"></i>
                Xem chi tiết
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nike-btn nike-btn-outline">
                <i class="fas fa-arrow-left"></i>
                Quay lại
            </a>
        </div>
    </div>
    
    <div class="card-body">
        @if($errors->any())
            <div class="form-error-summary">
                <strong>Chưa cập nhật được danh mục.</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h4 class="form-section-title">Thông tin cơ bản</h4>
                    
                    <div class="form-group">
                        <label for="name" class="form-label required">Tên danh mục</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $category->display_name) }}"
                               class="form-input @error('name') error @enderror"
                               required>
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="slug" class="form-label">Slug (URL)</label>
                        <input type="text" 
                               id="slug" 
                               name="slug" 
                               value="{{ old('slug', $category->slug) }}"
                               class="form-input @error('slug') error @enderror">
                        <div class="form-hint">
                            URL hiện tại: <strong>{{ $category->url }}</strong>
                        </div>
                        @error('slug')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Mô tả</label>
                        <textarea id="description" 
                                  name="description" 
                                  rows="4"
                                  class="form-input @error('description') error @enderror">{{ old('description', $category->description) }}</textarea>
                        @error('description')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Media & Display -->
                <div class="form-section">
                    <h4 class="form-section-title">Hình ảnh & Hiển thị</h4>
                    
                    <div class="form-group">
                        <label for="image" class="form-label">Hình ảnh danh mục</label>
                        <div class="image-upload-container">
                            <input type="file" 
                                   id="image" 
                                   name="image" 
                                   accept="image/*"
                                   class="image-input @error('image') error @enderror"
                                   onchange="previewImage(this)">
                            <div class="image-preview" id="imagePreview">
                                @if($category->display_image_url)
                                    <img src="{{ $category->display_image_url }}" alt="{{ $category->display_name }}">
                                    <div class="image-overlay">
                                        <span>Click để thay đổi</span>
                                    </div>
                                @else
                                    <div class="upload-placeholder">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Kéo thả hoặc click để chọn hình ảnh</span>
                                        <small>JPG, PNG, GIF, WEBP, AVIF, BMP, SVG tối đa 10MB</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if($category->image)
                            <div class="current-image-info">
                                <small class="text-muted">
                                    Hình ảnh hiện tại: {{ basename($category->image) }}
                                    <button type="button" class="btn-link" onclick="removeCurrentImage()">Xóa hình ảnh</button>
                                </small>
                            </div>
                        @endif
                        @error('image')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="icon" class="form-label">Icon (FontAwesome)</label>
                        <div class="icon-input-container">
                            <input type="text" 
                                   id="icon" 
                                   name="icon" 
                                   value="{{ old('icon', $category->icon) }}"
                                   class="form-input @error('icon') error @enderror">
                            <div class="icon-preview" id="iconPreview">
                                <i class="{{ old('icon', $category->icon) ?: 'fas fa-folder' }}"></i>
                            </div>
                        </div>
                        <div class="form-hint">
                            Mã icon FontAwesome. 
                            <a href="https://fontawesome.com/icons" target="_blank">Xem danh sách icon</a>
                        </div>
                        @error('icon')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Hierarchy & Organization -->
                <div class="form-section">
                    <h4 class="form-section-title">Phân cấp & Tổ chức</h4>
                    
                    <div class="form-group">
                        <label for="parent_id" class="form-label">Danh mục cha</label>
                        <select id="parent_id" 
                                name="parent_id" 
                                class="form-input @error('parent_id') error @enderror">
                            <option value="">Không có (Danh mục gốc)</option>
                            @foreach($parentCategories as $parentCategory)
                                <option value="{{ $parentCategory['id'] }}" 
                                        {{ old('parent_id', $category->parent_id) == $parentCategory['id'] ? 'selected' : '' }}>
                                    {{ $parentCategory['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @if($category->parent)
                            <div class="form-hint">
                                Danh mục cha hiện tại: <strong>{{ $category->parent->display_name }}</strong>
                            </div>
                        @endif
                        @error('parent_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="sort_order" class="form-label">Thứ tự hiển thị</label>
                        <input type="number" 
                               id="sort_order" 
                               name="sort_order" 
                               value="{{ old('sort_order', $category->sort_order) }}"
                               min="0"
                               class="form-input @error('sort_order') error @enderror">
                        <div class="form-hint">Số nhỏ hơn sẽ hiển thị trước.</div>
                        @error('sort_order')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Trạng thái</label>
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                <span class="checkbox-custom"></span>
                                Kích hoạt danh mục
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- SEO Settings -->
                <div class="form-section">
                    <h4 class="form-section-title">Cài đặt SEO</h4>
                    
                    <div class="form-group">
                        <label for="meta_title" class="form-label">Meta Title</label>
                        <input type="text" 
                               id="meta_title" 
                               name="meta_title" 
                               value="{{ old('meta_title', $category->meta_title) }}"
                               maxlength="255"
                               class="form-input @error('meta_title') error @enderror">
                        <div class="character-count">
                            <span id="metaTitleCount">{{ strlen($category->meta_title ?? '') }}</span>/255 ký tự
                        </div>
                        @error('meta_title')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea id="meta_description" 
                                  name="meta_description" 
                                  rows="3"
                                  maxlength="500"
                                  class="form-input @error('meta_description') error @enderror">{{ old('meta_description', $category->meta_description) }}</textarea>
                        <div class="character-count">
                            <span id="metaDescCount">{{ strlen($category->meta_description ?? '') }}</span>/500 ký tự
                        </div>
                        @error('meta_description')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <!-- Statistics & Info -->
            <div class="form-section" style="grid-column: 1 / -1; margin-top: 24px;">
                <h4 class="form-section-title">Thống kê danh mục</h4>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-label">Sản phẩm</div>
                        <div class="stat-value">{{ $category->total_products_count }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Danh mục con</div>
                        <div class="stat-value">{{ $category->children()->count() }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Ngày tạo</div>
                        <div class="stat-value">{{ $category->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Cập nhật cuối</div>
                        <div class="stat-value">{{ $category->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Hidden input for removing image -->
            <input type="hidden" name="remove_image" id="removeImage" value="0">
            
            <!-- Action Buttons -->
            <div class="form-actions">
                <button type="submit" class="nike-btn nike-btn-primary">
                    <i class="fas fa-save"></i>
                    Cập nhật danh mục
                </button>
                <a href="{{ route('admin.categories.show', $category) }}" class="nike-btn nike-btn-outline">
                    <i class="fas fa-eye"></i>
                    Xem chi tiết
                </a>
                <a href="{{ route('admin.categories.index') }}" class="nike-btn nike-btn-outline">
                    <i class="fas fa-times"></i>
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.form-error-summary {
    margin-bottom: 20px;
    padding: 14px 16px;
    border: 1px solid #fecaca;
    border-radius: 10px;
    background: #fef2f2;
    color: #991b1b;
}

.form-error-summary strong {
    display: block;
    margin-bottom: 8px;
}

.form-error-summary ul {
    margin: 0;
    padding-left: 18px;
}

.form-error-summary li {
    margin: 4px 0;
}

/* Form Grid Layout */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
    margin-bottom: 32px;
}

.form-section {
    background: #f8f9fa;
    padding: 24px;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.form-section-title {
    margin: 0 0 20px 0;
    color: #333;
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-section-title:before {
    content: '';
    width: 4px;
    height: 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-radius: 2px;
}

/* Form Elements */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.form-label.required:after {
    content: ' *';
    color: #dc3545;
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
}

.form-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input.error {
    border-color: #dc3545;
}

.form-error {
    color: #dc3545;
    font-size: 12px;
    margin-top: 4px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.form-error:before {
    content: '⚠';
}

.form-hint {
    color: #666;
    font-size: 12px;
    margin-top: 4px;
    line-height: 1.4;
}

.form-hint a {
    color: #667eea;
    text-decoration: none;
}

.form-hint a:hover {
    text-decoration: underline;
}

/* Image Upload */
.image-upload-container {
    position: relative;
}

.image-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    z-index: 2;
}

.image-preview {
    width: 100%;
    height: 200px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.image-preview:hover {
    border-color: #667eea;
    background: #f0f4ff;
}

.image-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: cover;
    border-radius: 6px;
}

.image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 500;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.image-preview:hover .image-overlay {
    opacity: 1;
}

.upload-placeholder {
    text-align: center;
    color: #666;
}

.upload-placeholder i {
    font-size: 32px;
    color: #ddd;
    margin-bottom: 8px;
    display: block;
}

.upload-placeholder span {
    display: block;
    margin-bottom: 4px;
    font-weight: 500;
}

.upload-placeholder small {
    color: #999;
    font-size: 11px;
}

.current-image-info {
    margin-top: 8px;
}

.btn-link {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    text-decoration: underline;
    font-size: 12px;
    margin-left: 8px;
}

.btn-link:hover {
    text-decoration: none;
}

/* Icon Input */
.icon-input-container {
    display: flex;
    gap: 12px;
    align-items: center;
}

.icon-input-container .form-input {
    flex: 1;
}

.icon-preview {
    width: 48px;
    height: 48px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    font-size: 18px;
    color: #667eea;
}

/* Checkbox */
.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: normal;
    color: #555;
}

.checkbox-label input[type="checkbox"] {
    display: none;
}

.checkbox-custom {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    background: white;
}

.checkbox-label input[type="checkbox"]:checked + .checkbox-custom {
    background: #667eea;
    border-color: #667eea;
}

.checkbox-label input[type="checkbox"]:checked + .checkbox-custom:after {
    content: '✓';
    color: white;
    font-size: 12px;
    font-weight: bold;
}

/* Character Count */
.character-count {
    text-align: right;
    color: #666;
    font-size: 11px;
    margin-top: 4px;
}

/* Statistics Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
}

.stat-item {
    text-align: center;
    padding: 16px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 4px;
}

.stat-value {
    font-size: 18px;
    font-weight: 700;
    color: #333;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 12px;
    padding-top: 24px;
    border-top: 2px solid #e9ecef;
    justify-content: flex-start;
}

/* Responsive */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .form-section {
        padding: 16px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .icon-input-container {
        flex-direction: column;
        align-items: stretch;
    }
    
    .icon-preview {
        align-self: center;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
// Auto-generate slug from name (only if not manually edited)
document.getElementById('name').addEventListener('input', function() {
    const name = this.value;
    const slugField = document.getElementById('slug');
    
    if (!slugField.dataset.manual) {
        const slug = name
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '') // Remove diacritics
            .replace(/[đĐ]/g, 'd')
            .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
            .replace(/\s+/g, '-') // Replace spaces with hyphens
            .replace(/-+/g, '-') // Replace multiple hyphens with single
            .replace(/^-|-$/g, ''); // Remove leading/trailing hyphens
        
        slugField.value = slug;
    }
});

// Mark slug as manually edited
document.getElementById('slug').addEventListener('input', function() {
    this.dataset.manual = 'true';
});

// Image preview
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <div class="image-overlay">
                    <span>Hình ảnh mới</span>
                </div>
            `;
        };
        
        reader.readAsDataURL(input.files[0]);
        
        // Reset remove image flag
        document.getElementById('removeImage').value = '0';
    }
}

// Remove current image
function removeCurrentImage() {
    const preview = document.getElementById('imagePreview');
    const removeInput = document.getElementById('removeImage');
    
    preview.innerHTML = `
        <div class="upload-placeholder">
            <i class="fas fa-cloud-upload-alt"></i>
            <span>Kéo thả hoặc click để chọn hình ảnh</span>
            <small>JPG, PNG, GIF, WEBP, AVIF, BMP, SVG tối đa 10MB</small>
        </div>
    `;
    
    removeInput.value = '1';
    
    // Clear file input
    document.getElementById('image').value = '';
}

// Icon preview
document.getElementById('icon').addEventListener('input', function() {
    const iconPreview = document.getElementById('iconPreview');
    const iconClass = this.value || 'fas fa-folder';
    
    iconPreview.innerHTML = `<i class="${iconClass}"></i>`;
});

// Character counting
function setupCharacterCount(inputId, countId, maxLength) {
    const input = document.getElementById(inputId);
    const counter = document.getElementById(countId);
    
    if (input && counter) {
        function updateCount() {
            const length = input.value.length;
            counter.textContent = length;
            
            if (length > maxLength * 0.9) {
                counter.style.color = '#dc3545';
            } else if (length > maxLength * 0.7) {
                counter.style.color = '#ffc107';
            } else {
                counter.style.color = '#666';
            }
        }
        
        input.addEventListener('input', updateCount);
        updateCount(); // Initial count
    }
}

// Initialize character counters
document.addEventListener('DOMContentLoaded', function() {
    setupCharacterCount('meta_title', 'metaTitleCount', 255);
    setupCharacterCount('meta_description', 'metaDescCount', 500);
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    
    if (!name) {
        e.preventDefault();
        alert('Vui lòng nhập tên danh mục');
        document.getElementById('name').focus();
        return;
    }
    
    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...';
});

// Show success/error messages
@if(session('success'))
    setTimeout(() => {
        alert('{{ session("success") }}');
    }, 100);
@endif

</script>
@endsection


