@php
    $statusCode   = 401;
    $icon         = 'lock';
    $iconClass    = '401';
    $errorTitle   = 'Autentikasi Diperlukan';
    $errorMessage = 'Sesi kamu telah berakhir atau kamu belum login. Silakan masuk terlebih dahulu untuk melanjutkan.';
@endphp
@include('errors.error')
