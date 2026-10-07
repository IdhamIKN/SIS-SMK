@php
    $statusCode   = 419;
    $icon         = 'clock';
    $iconClass    = '419';
    $errorTitle   = 'Sesi Kedaluwarsa';
    $errorMessage = 'Sesi kamu telah habis atau token keamanan (CSRF) tidak valid. Silakan login ulang untuk melanjutkan aktivitasmu.';
@endphp
@include('errors.error')
