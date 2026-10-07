{{--
    Partial: _jam_options.blade.php
    Render <option> atau <optgroup> untuk dropdown pilih jam pelajaran.

    Variables:
        $jamPelajaranGrouped  — array dari SetJam::getJamAktifGrouped()
        $selected             — id_jam yang pre-selected (optional)
        $placeholder          — teks placeholder (optional, default "— Pilih Jam —")
--}}
@php
    $placeholder ??= '— Pilih Jam —';
    $selected    ??= null;

    $kelompokLabel = [
        'reguler'      => '📅 Reguler – Kelas 10 (Senin–Kamis)',
        'reguler_1112' => '📅 Reguler – Kelas 11 & 12 (Senin–Kamis)',
        'jumat'        => '🕌 Jumat – Semua Kelas',
    ];
@endphp

<option value="">{{ $placeholder }}</option>

@foreach (['reguler', 'reguler_1112', 'jumat'] as $klp)
    @php $jamList = $jamPelajaranGrouped[$klp] ?? collect(); @endphp
    @if ($jamList->isNotEmpty())
        <optgroup label="{{ $kelompokLabel[$klp] }}">
            @foreach ($jamList as $jam)
                <option
                    value="{{ $jam->id_jam }}"
                    data-kelompok="{{ $jam->kelompok_jam }}"
                    data-mulai="{{ $jam->time_in ? $jam->time_in->format('H:i') : '' }}"
                    data-selesai="{{ $jam->time_out ? $jam->time_out->format('H:i') : '' }}"
                    data-time-in="{{ $jam->time_in ? $jam->time_in->format('H:i:s') : '' }}"
                    data-time-out="{{ $jam->time_out ? $jam->time_out->format('H:i:s') : '' }}"
                    {{ (string) $selected === (string) $jam->id_jam ? 'selected' : '' }}
                >
                    {{ $jam->nama_jam }}
                    ({{ $jam->time_in ? $jam->time_in->format('H:i') : '--' }}–{{ $jam->time_out ? $jam->time_out->format('H:i') : '--' }})
                </option>
            @endforeach
        </optgroup>
    @endif
@endforeach
