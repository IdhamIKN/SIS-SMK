@php
    $isEdit = isset($pasal);
    $detail = $detail ?? null;
    $selectedJenis = old('jenis', $jenis ?? 'pelanggaran');
    $selectedKategori = old('idkategori', $pasal->idkategori ?? '');
    $statusAktif = old('status_aktif', $isEdit ? (int) ($pasal->status_aktif ?? true) : 1);
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <div class="tatib-form-card">
        <div class="tatib-form-grid">
            <div class="tatib-field">
                <label>Jenis</label>
                <select name="jenis" class="tatib-select" data-jenis-select>
                    <option value="pelanggaran" @selected($selectedJenis === 'pelanggaran')>Pelanggaran</option>
                    <option value="penghargaan" @selected($selectedJenis === 'penghargaan')>Penghargaan</option>
                </select>
                @error('jenis') <span class="tatib-error">{{ $message }}</span> @enderror
            </div>

            <div class="tatib-field">
                <label>Kategori</label>
                <select name="idkategori" class="tatib-select" data-kategori-select required>
                    @foreach ($kategori as $item)
                        @php
                            $itemJenis = $item->idgroup === 'R' ? 'penghargaan' : 'pelanggaran';
                        @endphp
                        <option value="{{ $item->idkategori }}"
                            data-jenis="{{ $itemJenis }}"
                            @selected($selectedKategori === $item->idkategori)>
                            [{{ $item->idkategori }}] {{ $item->kategori }}
                        </option>
                    @endforeach
                </select>
                @error('idkategori') <span class="tatib-error">{{ $message }}</span> @enderror
            </div>

            <div class="tatib-field">
                <label>Kode Pasal</label>
                @if ($isEdit)
                    <input type="text" class="tatib-input" value="{{ $pasal->idpasal }}" readonly>
                @else
                    <input type="text" name="idpasal" class="tatib-input" value="{{ old('idpasal') }}"
                        maxlength="5" required style="text-transform:uppercase;">
                    @error('idpasal') <span class="tatib-error">{{ $message }}</span> @enderror
                @endif
            </div>

            <div class="tatib-field">
                <label>Urut</label>
                <input type="number" name="urut" class="tatib-input"
                    value="{{ old('urut', $pasal->urut ?? '') }}" min="0" max="9999">
                @error('urut') <span class="tatib-error">{{ $message }}</span> @enderror
            </div>

            <div class="tatib-field">
                <label>Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" class="tatib-input"
                    value="{{ old('tahun_ajaran', $tahunAjaran) }}" maxlength="9" required>
                @error('tahun_ajaran') <span class="tatib-error">{{ $message }}</span> @enderror
            </div>

            <div class="tatib-field">
                <label>Status</label>
                <label class="tatib-check">
                    <input type="checkbox" name="status_aktif" value="1" @checked((bool) $statusAktif)>
                    <span>Aktif</span>
                </label>
                @error('status_aktif') <span class="tatib-error">{{ $message }}</span> @enderror
            </div>

            <div class="tatib-field">
                <label>Skor Minimum</label>
                <input type="number" name="skormin" class="tatib-input"
                    value="{{ old('skormin', $detail->skormin ?? '') }}" min="0" max="999" required>
                @error('skormin') <span class="tatib-error">{{ $message }}</span> @enderror
            </div>

            <div class="tatib-field">
                <label>Skor Maksimum</label>
                <input type="number" name="skormax" class="tatib-input"
                    value="{{ old('skormax', $detail->skormax ?? '') }}" min="0" max="999" required>
                @error('skormax') <span class="tatib-error">{{ $message }}</span> @enderror
            </div>

            <div class="tatib-field full">
                <label>Uraian Pasal</label>
                <textarea name="pasal" class="tatib-textarea" required>{{ old('pasal', $detail->pasal ?? '') }}</textarea>
                @error('pasal') <span class="tatib-error">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    <div class="tatib-actions">
        <a href="{{ $backRoute }}" class="tatib-btn tatib-btn-soft"><i class="fas fa-arrow-left"></i> Kembali</a>
        <button type="submit" class="tatib-btn tatib-btn-primary"><i class="fas fa-save"></i> Simpan</button>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const jenisSelect = document.querySelector('[data-jenis-select]');
    const kategoriSelect = document.querySelector('[data-kategori-select]');

    function syncKategori() {
        if (!jenisSelect || !kategoriSelect) return;

        const selectedJenis = jenisSelect.value;
        let visibleSelected = false;

        Array.from(kategoriSelect.options).forEach(function (option) {
            const isVisible = option.dataset.jenis === selectedJenis;
            option.hidden = !isVisible;
            option.disabled = !isVisible;

            if (option.selected && isVisible) {
                visibleSelected = true;
            }
        });

        if (!visibleSelected) {
            const firstVisible = Array.from(kategoriSelect.options).find(function (option) {
                return !option.hidden;
            });

            if (firstVisible) {
                firstVisible.selected = true;
            }
        }
    }

    if (jenisSelect) {
        jenisSelect.addEventListener('change', syncKategori);
        syncKategori();
    }
});
</script>
@endpush
