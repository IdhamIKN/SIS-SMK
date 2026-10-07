@php
    $statusCode   = 500;
    $icon         = 'exclamation-triangle';
    $iconClass    = '500';
    $errorTitle   = 'Terjadi Kesalahan';
    $errorMessage = 'Maaf, terjadi kesalahan pada server. Silakan coba kembali beberapa saat lagi.';
@endphp
@include('errors.error')