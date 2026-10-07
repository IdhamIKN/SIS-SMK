<form id="dl-form" method="GET" action="{{ route('admin.dashboard-laporan.index') }}">
    <div class="dl-filter">
        <div class="dl-filter-title"><i class="fas fa-sliders-h"></i> Filter & Periode</div>

        {{-- Periode Cepat --}}
        <div style="margin-bottom:10px;">
            <div
                style="font-size:.65rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">
                Periode</div>
            <div class="dl-periode-btns">
                @foreach (['harian' => 'Hari Ini', 'mingguan' => 'Minggu Ini', 'bulanan' => 'Bulan Ini', 'tahunan' => 'Tahun Ini', 'custom' => 'Custom'] as $key => $label)
                    <button type="button" class="dl-btn-periode {{ $periode === $key ? 'active' : '' }}"
                        data-periode="{{ $key }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
            <input type="hidden" name="periode" id="input-periode" value="{{ $periode }}">
        </div>

        {{-- Custom Range --}}
        <div class="dl-custom-range {{ $periode === 'custom' ? 'show' : '' }}" id="custom-range">
            <div class="dl-filter-field">
                <label><i class="fas fa-calendar-day"></i> Dari Tanggal</label>
                <input type="text" name="date_from" id="date_from" class="dl-fld"
                    value="{{ $dateFrom->format('Y-m-d') }}" placeholder="yyyy-mm-dd">
            </div>
            <div class="dl-filter-field">
                <label><i class="fas fa-calendar-day"></i> Sampai Tanggal</label>
                <input type="text" name="date_to" id="date_to" class="dl-fld"
                    value="{{ $dateTo->format('Y-m-d') }}" placeholder="yyyy-mm-dd">
            </div>
        </div>

        {{-- Filter Tambahan --}}
        <div class="dl-filter-grid" style="margin-top:10px;padding-top:10px;border-top:1px solid #f1f5f9;">
            <div class="dl-filter-field">
                <label><i class="fas fa-chalkboard"></i> Kelas</label>
                <select name="kelas_id" class="dl-fld">
                    <option value="">Semua Kelas</option>
                    @foreach ($kelasList as $k)
                        <option value="{{ $k->id }}" @selected($kelasId == $k->id)>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="dl-filter-field">
                <label><i class="fas fa-layer-group"></i> Tingkat</label>
                <select name="tingkat" class="dl-fld">
                    <option value="">Semua</option>
                    @foreach (['X', 'XI', 'XII'] as $t)
                        <option value="{{ $t }}" @selected($tingkat === $t)>Kelas {{ $t }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="dl-filter-field">
                <label><i class="fas fa-sun"></i> Shift</label>
                <select name="shift" class="dl-fld">
                    <option value="">Semua</option>
                    <option value="Pagi" @selected($shift === 'Pagi')>Pagi</option>
                    <option value="Siang" @selected($shift === 'Siang')>Siang</option>
                </select>
            </div>
            <div class="dl-filter-field" style="align-self:end;">
                <div class="dl-filter-actions" style="margin-top:0;">
                    <button type="submit" class="dl-btn-apply">
                        <i class="fas fa-search"></i> Terapkan
                    </button>
                    <a href="{{ route('admin.dashboard-laporan.index') }}" class="dl-btn-reset">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </div>

        {{-- Periode indicator --}}
        <div style="margin-top:10px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
            <span
                style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;background:#eef2ff;color:#4338ca;font-size:.68rem;font-weight:700;border:1px solid #c7d2fe;">
                <i class="fas fa-calendar-alt"></i>
                {{ $dateFrom->translatedFormat('d M Y') }} — {{ $dateTo->translatedFormat('d M Y') }}
                ({{ (int) $dateFrom->diffInDays($dateTo) + 1 }} hari)
            </span>
            <button id="dl-print-btn" type="button" onclick="window.dlPrintAllCharts()"
                style="display:inline-flex;align-items:center;gap:6px;padding:4px 14px;
                   border-radius:20px;background:#6366f1;color:#fff;font-size:.68rem;
                   font-weight:700;border:none;cursor:pointer;
                   box-shadow:0 2px 8px rgba(99,102,241,.3);transition:opacity .15s;">
                <i class="fas fa-print"></i> Cetak Semua Chart
            </button>
        </div>
    </div>
</form>
