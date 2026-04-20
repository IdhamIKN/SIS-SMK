{{--
    Komponen: Confirmation Modal Azures
    Penggunaan: @include('components.azures.confirm-modal', ['id' => 'delete-modal', 'title' => 'Konfirmasi Hapus'])

    Props:
    - id: Modal ID (required)
    - title: Judul modal (required)
    - message: Pesan konfirmasi (required)
    - confirmText: Text tombol konfirmasi (default: 'Hapus')
    - confirmColor: Warna tombol konfirmasi (default: 'red')
    - cancelText: Text tombol batal (default: 'Batal')
    - icon: Icon untuk modal (default: 'fas fa-exclamation-triangle')
--}}

{{-- Hidden modal trigger for delete actions --}}
<div id="{{ $id }}" class="menu menu-box-modal rounded-m" data-modal-id="{{ $id }}">
    <div class="menu-header">
        <h5 class="font-700">{{ $title ?? 'Konfirmasi' }}</h5>
        <a href="#" class="close-menu">
            <i class="fa fa-times"></i>
        </a>
    </div>

    <div class="menu-content text-center">
        @if(isset($icon) && $icon)
            <div class="mb-3">
                <i class="{{ $icon }} fa-3x color-{{ $confirmColor ?? 'red' }}-dark opacity-70"></i>
            </div>
        @else
            <div class="mb-3">
                <i class="fas fa-exclamation-triangle fa-3x color-red-dark opacity-70"></i>
            </div>
        @endif

        <h4 class="font-600 mb-3">{{ $message ?? 'Apakah Anda yakin?' }}</h4>

        @if(isset($warning) && $warning)
            <div class="alert bg-orange-dark color-white mb-3 rounded-s">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ $warning }}
            </div>
        @endif

        <div class="row mb-0">
            <div class="col-6">
                <a href="#" class="btn btn-m btn-full btn-border border-gray color-gray rounded-s close-menu">
                    <i class="fas fa-times me-2"></i>{{ $cancelText ?? 'Batal' }}
                </a>
            </div>
            <div class="col-6">
                <a href="#" id="confirm-action-{{ $id }}"
                   class="btn btn-m btn-full bg-{{ $confirmColor ?? 'red' }}-dark rounded-s"
                   onclick="executeConfirmAction('{{ $id }}')">
                    <i class="fas fa-check me-2"></i>{{ $confirmText ?? 'Hapus' }}
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal confirmation specific styles */
.menu-box-modal {
    max-width: 400px;
    margin: 20px auto;
}

.menu-content.text-center {
    padding: 20px;
}

.menu-content .alert {
    font-size: 12px;
    padding: 10px;
    margin-bottom: 15px;
}
</style>

<script>
// Global function to handle confirm actions
function executeConfirmAction(modalId) {
    // This function should be overridden by specific implementations
    console.log('Confirm action for modal:', modalId);
}

// Helper function to show confirmation modal
function showConfirmModal(modalId, actionUrl, method = 'POST') {
    // Store action details
    const modal = document.getElementById(modalId);
    const confirmBtn = document.getElementById('confirm-action-' + modalId);

    if (confirmBtn) {
        confirmBtn.onclick = function(e) {
            e.preventDefault();

            // Create form for delete action
            const form = document.createElement('form');
            form.method = method;
            form.action = actionUrl;
            form.style.display = 'none';

            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }

            // Add method spoofing for DELETE, PUT, PATCH
            if (method !== 'POST') {
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = method;
                form.appendChild(methodInput);
            }

            document.body.appendChild(form);
            form.submit();

            // Close modal
            modal.classList.remove('menu-active');
        };
    }

    // Show modal
    modal.classList.add('menu-active');
}
</script>