{{--
    Komponen: Alert Messages Azures
    Penggunaan: @include('components.azures.alerts')

    Otomatis menampilkan session flash messages dengan styling Azures
--}}

{{-- Success Alert --}}
@if(session('success'))
<div class="alert bg-green-dark color-white mb-3 rounded-s">
    <i class="fas fa-check-circle me-2"></i>
    {{ session('success') }}
    <button type="button" class="close-alert" style="position: absolute; right: 10px; top: 10px;">
        <i class="fas fa-times"></i>
    </button>
</div>
@endif

@php
    $validationErrors = $errors ?? null;
    $hasValidationErrors = is_object($validationErrors)
        && method_exists($validationErrors, 'any')
        && $validationErrors->any();
@endphp

{{-- Error Alert --}}
@if(session('error') || $hasValidationErrors)
<div class="alert bg-red-dark color-white mb-3 rounded-s">
    <i class="fas fa-exclamation-triangle me-2"></i>
    @if(session('error'))
        {{ session('error') }}
    @elseif($hasValidationErrors)
        {{ $validationErrors->first() }}
    @endif
    <button type="button" class="close-alert" style="position: absolute; right: 10px; top: 10px;">
        <i class="fas fa-times"></i>
    </button>
</div>
@endif

{{-- Warning Alert --}}
@if(session('warning'))
<div class="alert bg-orange-dark color-white mb-3 rounded-s">
    <i class="fas fa-exclamation-circle me-2"></i>
    {{ session('warning') }}
    <button type="button" class="close-alert" style="position: absolute; right: 10px; top: 10px;">
        <i class="fas fa-times"></i>
    </button>
</div>
@endif

{{-- Info Alert --}}
@if(session('info'))
<div class="alert bg-blue-dark color-white mb-3 rounded-s">
    <i class="fas fa-info-circle me-2"></i>
    {{ session('info') }}
    <button type="button" class="close-alert" style="position: absolute; right: 10px; top: 10px;">
        <i class="fas fa-times"></i>
    </button>
</div>
@endif

<style>
.alert {
    position: relative;
    padding: 15px 40px 15px 15px;
    margin-bottom: 15px;
    border-radius: 8px;
    font-size: 14px;
    line-height: 1.4;
}

.close-alert {
    background: none;
    border: none;
    color: rgba(255,255,255,0.7);
    font-size: 12px;
    cursor: pointer;
    padding: 5px;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.close-alert:hover {
    background-color: rgba(255,255,255,0.1);
    color: white;
}
</style>

<script>
// Auto-close alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Manual close on click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('close-alert') || e.target.closest('.close-alert')) {
            const alert = e.target.closest('.alert');
            if (alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }
        }
    });
});
</script>
