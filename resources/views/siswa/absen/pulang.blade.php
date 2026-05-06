@extends('layouts.app')

@section('title', 'Absen Pulang')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-6 text-center">
            Absen Pulang
        </h1>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="absenForm" method="POST" action="{{ route('absen.store', 'pulang') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="text-center">
                <div id="selfiePreview" class="w-48 h-48 mx-auto bg-gray-200 rounded-full flex items-center justify-center border-4 border-dashed border-gray-400 mb-4 overflow-hidden" style="aspect-ratio: 1;">
                    <div class="text-gray-500 text-lg">
                        Ambil Selfie Pulang
                    </div>
                </div>
                <div id="selfieCanvas" style="display: none;"></div>
                <input type="file" id="fotoSelfie" name="foto_selfie" accept="image/*" capture="environment" class="hidden">
                <button type="button" id="captureBtn" class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-2 rounded-lg font-medium transition duration-200">
                    📷 Ambil Selfie
                </button>
            </div>

            <div class="text-sm text-gray-600">
                <p id="locationInfo">📍 Mendeteksi lokasi...</p>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
                <input type="hidden" name="jarak" id="jarak">
            </div>

            <div id="distanceWarning" class="hidden bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded text-sm">
                ⚠ Lokasi Anda terlalu jauh dari sekolah ({{ config('sekolah.radius_m') }}m).
            </div>

            <button type="submit" id="submitBtn" disabled class="w-full bg-indigo-500 hover:bg-indigo-600 text-white py-3 px-4 rounded-lg font-semibold text-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                🏠 Konfirmasi Absen Pulang
            </button>
        </form>

        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
            <h3 class="font-semibold text-gray-900 mb-2">Status Hari Ini</h3>
            <div id="statusHariIni">
                Memeriksa status absen...
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Sama seperti masuk.blade.php tapi action route('absen.pulang.store')
    const video = document.createElement('video');
    const canvas = document.getElementById('selfieCanvas');
    const ctx = canvas.getContext('2d');
    const preview = document.getElementById('selfiePreview');
    const captureBtn = document.getElementById('captureBtn');
    const fotoInput = document.getElementById('fotoSelfie');
    const submitBtn = document.getElementById('submitBtn');
    const locationInfo = document.getElementById('locationInfo');
    const distanceWarning = document.getElementById('distanceWarning');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    const jarakInput = document.getElementById('jarak');
    const form = document.getElementById('absenForm');

    let stream = null;
    let currentPhoto = null;

    // Camera selfie
    captureBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'user' } 
            });
            video.srcObject = stream;
            video.play();

            canvas.width = 320;
            canvas.height = 320;

            const videoTrack = stream.getVideoTracks()[0];
            const imageCapture = new ImageCapture(videoTrack);

            // Auto capture 5s
            setTimeout(async () => {
                const photoBlob = await imageCapture.takePhoto();
                const photoUrl = URL.createObjectURL(photoBlob);
                preview.style.backgroundImage = `url(${photoUrl})`;
                preview.classList.remove('flex', 'items-center', 'justify-center');
                currentPhoto = photoBlob;
                fotoInput.files = new File([photoBlob], 'selfie-pulang.jpg', { type: 'image/jpeg' });
                captureBtn.textContent = '📷 Ulangi';
                checkAllReady();
            }, 5000);

        } catch (e) {
            alert('Kamera error: ' + e.message);
        }
    });

    // GPS & distance check
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            position => {
                latitudeInput.value = position.coords.latitude;
                longitudeInput.value = position.coords.longitude;
                checkLocation();
            },
            () => locationInfo.textContent = '❌ Izin GPS ditolak',
            { enableHighAccuracy: true }
        );
    }

    function checkLocation() {
        fetch('{{ route('absen.distance-check') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({
                latitude: latitudeInput.value,
                longitude: longitudeInput.value
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.valid) {
                locationInfo.innerHTML = `✅ ${data.jarak.toFixed(0)}m dari sekolah`;
                jarakInput.value = data.jarak;
                checkAllReady();
            } else {
                locationInfo.innerHTML = `❌ ${data.jarak.toFixed(0)}m > {{ config('sekolah.radius_m') }}m`;
                distanceWarning.classList.remove('hidden');
            }
        });
    }

    function checkAllReady() {
        if (currentPhoto && latitudeInput.value && parseFloat(jarakInput.value) <= {{ config('sekolah.radius_m') }}) {
            submitBtn.disabled = false;
        }
    }
});
</script>

@endsection

