{{--
    Komponen: File Upload with Preview Azures
    Penggunaan: @include('components.azures.file-upload', ['name' => '...', 'label' => '...'])

    Props:
    - name: Nama field (required)
    - label: Label text (required)
    - accept: File types yang diterima (default: 'image/*')
    - maxSize: Ukuran max dalam MB (default: 2)
    - preview: URL gambar existing (optional)
    - required: Apakah required (default: false)
    - help: Text bantuan (optional)
    - error: Error message (optional)
--}}

<div class="file-upload-container">
    <div class="input-style has-borders has-icon validate-field mb-4">
        <i class="fa fa-{{ isset($preview) && $preview ? 'edit' : 'camera' }}"></i>
        <input type="file" name="{{ $name }}" id="{{ $name }}"
               class="form-control file-input" accept="{{ $accept ?? 'image/*' }}"
               {{ $required ?? false ? 'required' : '' }}>
        <label class="color-highlight">{{ $label }}</label>
        <i class="fa fa-times disabled invalid color-red-dark"></i>
        <i class="fa fa-check disabled valid color-green-dark"></i>
        <em>{{ $help ?? 'Format: ' . ($accept ?? 'image/*') . ', Max: ' . ($maxSize ?? 2) . 'MB' }}</em>
    </div>

    {{-- Preview Container --}}
    <div class="preview-container {{ isset($preview) && $preview ? '' : 'd-none' }}" id="preview-{{ $name }}">
        <div class="card card-style">
            <div class="content">
                <div class="d-flex align-items-center">
                    <img id="preview-img-{{ $name }}" src="{{ $preview ?? '' }}"
                         class="rounded-s shadow-l me-3" width="80" height="80" style="object-fit: cover;">
                    <div class="flex-fill">
                        <h5 class="font-600 mb-1">Preview</h5>
                        <p class="font-12 opacity-70 mb-0">File akan diganti dengan yang baru</p>
                    </div>
                    <button type="button" class="btn btn-s btn-border border-red color-red rounded-s remove-preview">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Error Message --}}
    @if(isset($error) && $error)
        <p class="mt-1 text-sm text-red-600 font-12">{{ $error }}</p>
    @endif
</div>

<style>
.file-upload-container .preview-container {
    margin-top: 15px;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.file-upload-container .remove-preview {
    width: 40px;
    height: 40px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('{{ $name }}');
    const previewContainer = document.getElementById('preview-{{ $name }}');
    const previewImg = document.getElementById('preview-img-{{ $name }}');
    const removeBtn = previewContainer.querySelector('.remove-preview');

    // File change handler
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            const maxSize = {{ $maxSize ?? 2 }} * 1024 * 1024; // MB to bytes
            if (file.size > maxSize) {
                snackbar('File terlalu besar! Max {{ $maxSize ?? 2 }}MB', 'bg-red-dark', 3000);
                this.value = '';
                return;
            }

            // Validate file type
            const accept = '{{ $accept ?? 'image/*' }}';
            if (accept !== '*' && accept !== 'image/*') {
                const allowedTypes = accept.split(',').map(type => type.trim());
                if (!allowedTypes.some(type => file.type.match(type.replace('*', '.*')))) {
                    snackbar('Tipe file tidak didukung!', 'bg-red-dark', 3000);
                    this.value = '';
                    return;
                }
            }

            // Show preview for images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                // For non-image files, show file info
                previewImg.src = 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><rect width="80" height="80" fill="#e9ecef"/><text x="40" y="45" text-anchor="middle" font-family="Arial" font-size="12" fill="#6c757d">' + file.name.split('.').pop().toUpperCase() + '</text></svg>');
                previewContainer.classList.remove('d-none');
            }
        }
    });

    // Remove preview
    removeBtn.addEventListener('click', function() {
        fileInput.value = '';
        previewContainer.classList.add('d-none');
        previewImg.src = '';
    });
});
</script>