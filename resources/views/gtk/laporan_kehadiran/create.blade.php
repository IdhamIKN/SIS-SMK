@extends('layouts.app')

@section('title', 'Buat Laporan Kehadiran')

@section('content')

    {{-- Page Title --}}
    <div class="page-title page-title-large">
        <h2 data-username="{{ auth()->user()->name }}" class="greeting-text"></h2>
        <a href="#" data-menu="menu-main"
            class="bg-fade-highlight-light shadow-xl preload-img"
            data-src="{{ auth()->user()->avatar ? Storage::url(auth()->user()->avatar) : asset('azures/images/avatars/5s.png') }}">
        </a>
    </div>

    {{-- Header Card --}}
    <div class="card header-card shape-rounded" data-card-height="120">
        <div class="card-overlay bg-highlight opacity-95"></div>
        <div class="card-overlay dark-mode-tint"></div>
        <div class="card-bg preload-img" data-src="{{ asset('azures/images/pictures/20s.jpg') }}"></div>
    </div>

    {{-- Form Card --}}
    <div class="card card-style">
        <div class="content mb-0">

            {{-- Info --}}
            <div class="text-center mb-4">
                <i class="fas fa-clipboard-check fa-3x color-highlight mb-3"></i>
                <h4 class="font-700 mb-1">Laporan Kehadiran</h4>
                <p class="font-12 opacity-70">Laporkan kehadiran Anda hari ini</p>
            </div>

            {{-- Form --}}
            <form id="laporanForm" method="POST" action="{{ route('gtk.laporan-kehadiran.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Jenis Laporan --}}
                <div class="mb-4">
                    <label class="color-highlight font-14 mb-2 d-block">Jenis Laporan</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="radio" name="jenis" value="masuk" id="jenis_masuk" class="form-check-input" checked>
                            <label for="jenis_masuk" class="form-check-label ms-2">Absen Masuk</label>
                        </div>
                        <div class="col-6">
                            <input type="radio" name="jenis" value="pulang" id="jenis_pulang" class="form-check-input">
                            <label for="jenis_pulang" class="form-check-label ms-2">Absen Pulang</label>
                        </div>
                    </div>
                </div>

                {{-- Status --}}
                <div class="mb-4">
                    <label class="color-highlight font-14 mb-2 d-block">Status Kehadiran</label>
                    <select name="status" id="statusSelect" class="form-control rounded-s" required>
                        <option value="">Pilih Status</option>
                        <option value="hadir">Hadir</option>
                        <option value="sakit">Sakit</option>
                        <option value="izin">Izin</option>
                        <option value="alfa">Alfa</option>
                    </select>
                </div>

                {{-- Foto Selfie (hanya untuk hadir) --}}
                <div class="mb-4" id="fotoSection" style="display: none;">
                    <label class="color-highlight font-14 mb-2 d-block">Foto Selfie</label>
                    <div class="card card-style mb-3" style="position: relative; overflow: hidden;">
                        <video id="camera" class="w-100" autoplay muted playsinline style="max-height: 300px;"></video>
                        <div class="card-overlay bg-black opacity-20"></div>
                        <button type="button" id="captureBtn" class="btn btn-m btn-full bg-highlight rounded-s text-uppercase font-900 position-absolute" style="bottom: 15px; left: 15px; right: 15px;">
                            <i class="fas fa-camera me-2"></i>Ambil Foto
                        </button>
                    </div>
                    <input type="file" name="foto_selfie" id="fotoInput" accept="image/*" class="d-none" required>
                    <canvas id="canvas" class="d-none"></canvas>

                    <div id="preview" class="d-none">
                        <div class="card card-style">
                            <div class="content">
                                <img id="previewImg" class="w-100 rounded-s">
                                <p class="font-12 color-highlight mt-2 text-center">Foto berhasil diambil</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Location (hanya untuk hadir) --}}
                <div class="mb-4" id="locationSection" style="display: none;">
                    <label class="color-highlight font-14 mb-2 d-block">Lokasi</label>
                    <button type="button" id="getLocationBtn" class="btn btn-m btn-full bg-green-dark rounded-s text-uppercase font-900">
                        <i class="fas fa-map-marker-alt me-2"></i>Dapatkan Lokasi
                    </button>
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    <div id="locationInfo" class="mt-2 p-2 bg-fade-green-light rounded-s d-none">
                        <i class="fas fa-check-circle color-green-dark me-2"></i>
                        <span class="font-12 color-green-dark">Lokasi berhasil didapatkan</span>
                    </div>
                </div>

                {{-- Catatan --}}
                <div class="mb-4">
                    <label class="color-highlight font-14 mb-2 d-block">Catatan <span class="font-12 opacity-60">(Opsional)</span></label>
                    <textarea name="catatan" class="form-control rounded-s" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>

                {{-- Submit Button --}}
                <button type="submit" id="submitBtn" class="btn btn-m btn-full bg-highlight rounded-s text-uppercase font-900 disabled">
                    <i class="fas fa-paper-plane me-2"></i>Kirim Laporan
                </button>

                {{-- Info --}}
                <div class="text-center mt-3">
                    <p class="font-11 opacity-60 mb-0">
                        Pastikan data sudah benar sebelum kirim
                    </p>
                </div>
            </form>

        </div>
    </div>

@endsection

@push('scripts')
<script>
// Form handling
const statusSelect = document.getElementById('statusSelect');
const fotoSection = document.getElementById('fotoSection');
const locationSection = document.getElementById('locationSection');
const submitBtn = document.getElementById('submitBtn');

statusSelect.addEventListener('change', function() {
    const isHadir = this.value === 'hadir';
    fotoSection.style.display = isHadir ? 'block' : 'none';
    locationSection.style.display = isHadir ? 'block' : 'none';

    // Update required attributes
    document.getElementById('fotoInput').required = isHadir;
    document.getElementById('latitude').required = isHadir;
    document.getElementById('longitude').required = isHadir;

    // Enable/disable submit based on form state
    checkFormReady();
});

// Camera functionality
let stream = null;
const camera = document.getElementById('camera');
const captureBtn = document.getElementById('captureBtn');
const fotoInput = document.getElementById('fotoInput');
const canvas = document.getElementById('canvas');
const preview = document.getElementById('preview');
const previewImg = document.getElementById('previewImg');

// Initialize camera when status is hadir
statusSelect.addEventListener('change', function() {
    if (this.value === 'hadir') {
        initCamera();
    } else {
        stopCamera();
    }
});

async function initCamera() {
    try {
        const constraints = {
            video: {
                facingMode: 'user',
                width: { ideal: 640 },
                height: { ideal: 480 }
            }
        };
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        camera.srcObject = stream;

        camera.onloadedmetadata = () => {
            captureBtn.classList.remove('disabled');
        };
    } catch (error) {
        console.error('Camera error:', error);
        snackbar('Tidak dapat mengakses kamera: ' + error.message, 'bg-red-dark', 5000);
        captureBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Kamera Tidak Tersedia';
        captureBtn.classList.add('disabled');
    }
}

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    camera.srcObject = null;
}

// Capture photo
captureBtn.addEventListener('click', () => {
    if (captureBtn.classList.contains('disabled')) return;

    try {
        const context = canvas.getContext('2d');
        canvas.width = camera.videoWidth;
        canvas.height = camera.videoHeight;
        context.drawImage(camera, 0, 0);

        canvas.toBlob((blob) => {
            const file = new File([blob], 'laporan_' + Date.now() + '.jpg', { type: 'image/jpeg' });
            const dt = new DataTransfer();
            dt.items.add(file);
            fotoInput.files = dt.files;

            previewImg.src = canvas.toDataURL('image/jpeg', 0.8);
            preview.classList.remove('d-none');
            camera.style.display = 'none';
            captureBtn.innerHTML = '<i class="fas fa-redo me-2"></i>Ambil Ulang';
            captureBtn.classList.remove('bg-highlight');
            captureBtn.classList.add('bg-orange-dark');

            checkFormReady();
        }, 'image/jpeg', 0.8);
    } catch (error) {
        console.error('Capture error:', error);
        snackbar('Gagal mengambil foto', 'bg-red-dark', 3000);
    }
});

// Location functionality
const getLocationBtn = document.getElementById('getLocationBtn');
const latitudeInput = document.getElementById('latitude');
const longitudeInput = document.getElementById('longitude');
const locationInfo = document.getElementById('locationInfo');

getLocationBtn.addEventListener('click', () => {
    if (!navigator.geolocation) {
        snackbar('Geolokasi tidak didukung browser ini', 'bg-red-dark', 3000);
        return;
    }

    getLocationBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mendapatkan Lokasi...';
    getLocationBtn.classList.add('disabled');

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            latitudeInput.value = lat;
            longitudeInput.value = lon;
            locationInfo.classList.remove('d-none');
            getLocationBtn.innerHTML = '<i class="fas fa-check me-2"></i>Lokasi Didapatkan';
            getLocationBtn.classList.remove('disabled', 'bg-green-dark');
            getLocationBtn.classList.add('bg-green-dark');

            checkFormReady();

            console.log('Location obtained:', lat, lon);
        },
        (error) => {
            console.error('Location error:', error);
            let message = 'Gagal mendapatkan lokasi';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    message = 'Izin lokasi ditolak. Izinkan akses lokasi di browser.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    message = 'Lokasi tidak tersedia';
                    break;
                case error.TIMEOUT:
                    message = 'Timeout mendapatkan lokasi';
                    break;
            }
            snackbar(message, 'bg-red-dark', 5000);

            getLocationBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Lokasi Gagal';
            getLocationBtn.classList.remove('disabled');
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 60000
        }
    );
});

// Check if form is ready to submit
function checkFormReady() {
    const status = statusSelect.value;
    if (!status) {
        submitBtn.classList.add('disabled');
        return;
    }

    if (status === 'hadir') {
        const hasPhoto = fotoInput.files.length > 0;
        const hasLocation = latitudeInput.value && longitudeInput.value;
        if (hasPhoto && hasLocation) {
            submitBtn.classList.remove('disabled');
            submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Kirim Laporan';
        } else {
            submitBtn.classList.add('disabled');
        }
    } else {
        // Untuk status selain hadir, langsung enable
        submitBtn.classList.remove('disabled');
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Kirim Laporan';
    }
}

// Form submission
document.getElementById('laporanForm').addEventListener('submit', (e) => {
    const status = statusSelect.value;
    if (status === 'hadir') {
        if (!latitudeInput.value || !longitudeInput.value) {
            e.preventDefault();
            snackbar('Silakan dapatkan lokasi terlebih dahulu', 'bg-red-dark', 3000);
            return;
        }
        if (!fotoInput.files.length) {
            e.preventDefault();
            snackbar('Silakan ambil foto selfie terlebih dahulu', 'bg-red-dark', 3000);
            return;
        }
    }

    submitBtn.innerHTML = '<div class="d-flex align-items-center justify-content-center"><div class="spinner-border spinner-border-sm color-white me-2" role="status"><span class="visually-hidden">Loading...</span></div>Mengirim...</div>';
    submitBtn.classList.add('disabled');

    const formElements = document.querySelectorAll('#laporanForm input, #laporanForm select, #laporanForm textarea, #laporanForm button');
    formElements.forEach(element => {
        if (element !== submitBtn) {
            element.disabled = true;
        }
    });
});

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    statusSelect.dispatchEvent(new Event('change'));

    // Toast notifications
    @if(session('success'))
        snackbar("{{ session('success') }}", 'bg-green-dark', 4000);
    @endif

    @if($errors->any())
        snackbar("{{ $errors->first() }}", 'bg-red-dark', 4000);
    @endif
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    stopCamera();
});
</script>
@endpush