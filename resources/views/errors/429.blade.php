@php
    $statusCode   = 429;
    $icon         = 'tachometer-alt';
    $iconClass    = '429';
    $errorTitle   = 'Terlalu Banyak Permintaan';
    $errorMessage = 'Kamu mengirim terlalu banyak permintaan dalam waktu singkat. Tunggu beberapa saat lalu coba lagi.';
@endphp
@include('errors.error')
