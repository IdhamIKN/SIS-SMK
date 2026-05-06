{{--
    Props:
    - $selectId    : string  — unique ID (e.g. 'kelas', 'siswa')
    - $inputName   : string  — nama input form (e.g. 'kelas_id[]')
    - $placeholder : string  — placeholder search
    - $searchUrl   : string  — URL endpoint AJAX
    - $label       : string  — label satuan (e.g. 'kelas', 'siswa')
    - $initialData : array   — [{id, text, meta}] untuk pre-selected
--}}

<style>
.ajax-ms-wrap { position: relative; }
.ajax-chips-wrap { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; min-height: 0; }
.ajax-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; background: #e0f2fe; color: #0369a1;
    border-radius: 20px; font-size: .75rem; font-weight: 600;
}
.ajax-chip button {
    background: none; border: none; cursor: pointer; padding: 0;
    color: #0369a1; font-size: 1rem; line-height: 1;
    display: flex; align-items: center; justify-content: center;
    width: 16px; height: 16px; border-radius: 50%;
    transition: background .15s;
}
.ajax-chip button:hover { background: #bae6fd; }
.ajax-ms-search-wrap { position: relative; }
.ajax-ms-dropdown {
    display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
    background: #fff; border: 1px solid var(--border,#e2e8f0);
    border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,.1);
    z-index: 100; max-height: 240px; overflow-y: auto;
}
.ajax-ms-loading { padding: 12px; text-align: center; font-size: .8rem; color: #94a3b8; }
.ajax-ms-empty   { padding: 12px; text-align: center; font-size: .8rem; color: #94a3b8; }
.multi-select-item { display: flex; align-items: center; gap: 8px; padding: 9px 12px; cursor: pointer; font-size: .82rem; transition: background .12s; }
.multi-select-item:hover { background: #f1f5f9; }
.multi-select-item.selected { background: #e0f2fe; }
.multi-select-item input[type="checkbox"] { width:15px; height:15px; accent-color:#0ea5e9; pointer-events:none; flex-shrink:0; }
.item-meta { font-size:.7rem; color:var(--text-muted,#64748b); margin-left:auto; }
.multi-select-count { font-size:.72rem; color:var(--text-muted,#64748b); padding:6px 0 0; }
</style>

<div class="ajax-ms-wrap" id="ms_wrap_{{ $selectId }}">
    {{-- Chips: item yang sudah dipilih --}}
    <div class="ajax-chips-wrap" id="ms_chips_{{ $selectId }}"></div>

    {{-- Search input --}}
    <div class="ajax-ms-search-wrap">
        <input
            type="text"
            id="ms_search_{{ $selectId }}"
            class="multi-select-search form-input"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
        >
        <div class="ajax-ms-dropdown" id="ms_dropdown_{{ $selectId }}"></div>
    </div>

    {{-- Hidden inputs (di-inject JS) --}}
    <div id="ms_hidden_{{ $selectId }}"></div>

    <div class="multi-select-count" id="ms_count_{{ $selectId }}">0 {{ $label }} dipilih</div>
</div>

<script>
(function() {
    const CONFIG = {
        selectId:   '{{ $selectId }}',
        inputName:  '{{ $inputName }}',
        searchUrl:  '{{ $searchUrl }}',
        label:      '{{ $label }}',
        initial:    @json($initialData ?? []),
    };

    // ── State ────────────────────────────────────────────────
    const selected     = new Map(); // id(string) → {id, text, meta}
    let   lastResults  = [];
    let   debounceTimer;

    // ── DOM refs ─────────────────────────────────────────────
    const wrap      = document.getElementById('ms_wrap_'     + CONFIG.selectId);
    const chipsEl   = document.getElementById('ms_chips_'    + CONFIG.selectId);
    const searchEl  = document.getElementById('ms_search_'   + CONFIG.selectId);
    const dropEl    = document.getElementById('ms_dropdown_' + CONFIG.selectId);
    const hiddenEl  = document.getElementById('ms_hidden_'   + CONFIG.selectId);
    const countEl   = document.getElementById('ms_count_'    + CONFIG.selectId);

    // ── Init pre-selected ─────────────────────────────────────
    CONFIG.initial.forEach(item => selected.set(String(item.id), item));
    renderChips();
    renderHiddenInputs();
    updateCount();

    // ── Events ────────────────────────────────────────────────
    searchEl.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        dropEl.innerHTML = '<div class="ajax-ms-loading"><i class="fas fa-circle-notch fa-spin"></i> Mencari...</div>';
        dropEl.style.display = 'block';
        debounceTimer = setTimeout(fetchResults, 300);
    });

    searchEl.addEventListener('focus', () => {
        if (lastResults.length > 0) { renderDropdown(); dropEl.style.display = 'block'; }
        else fetchResults();
    });

    document.addEventListener('click', (e) => {
        if (!wrap.contains(e.target)) dropEl.style.display = 'none';
    });

    // ── Fetch ─────────────────────────────────────────────────
    async function fetchResults() {
        const q = searchEl.value.trim();
        try {
            const resp = await fetch(`${CONFIG.searchUrl}?q=${encodeURIComponent(q)}`);
            lastResults = await resp.json();
            renderDropdown();
            dropEl.style.display = 'block';
        } catch (err) {
            dropEl.innerHTML = '<div class="ajax-ms-empty">Gagal memuat data</div>';
        }
    }

    // ── Render dropdown ───────────────────────────────────────
    function renderDropdown() {
        dropEl.innerHTML = '';
        if (!lastResults.length) {
            dropEl.innerHTML = '<div class="ajax-ms-empty">Tidak ada hasil</div>';
            return;
        }
        lastResults.forEach(item => {
            const isSelected = selected.has(String(item.id));
            const div = document.createElement('div');
            div.className = 'multi-select-item' + (isSelected ? ' selected' : '');
            div.innerHTML =
                `<input type="checkbox" ${isSelected ? 'checked' : ''}>` +
                `<span>${escHtml(item.text)}</span>` +
                `<span class="item-meta">${escHtml(item.meta || '')}</span>`;
            div.addEventListener('mousedown', (e) => {
                e.preventDefault(); // jangan blur search
                toggle(item);
            });
            dropEl.appendChild(div);
        });
    }

    // ── Toggle item ───────────────────────────────────────────
    function toggle(item) {
        const key = String(item.id);
        if (selected.has(key)) selected.delete(key);
        else selected.set(key, item);
        renderChips();
        renderHiddenInputs();
        updateCount();
        renderDropdown();
    }

    // ── Render chips ──────────────────────────────────────────
    function renderChips() {
        chipsEl.innerHTML = '';
        selected.forEach((item, key) => {
            const chip = document.createElement('span');
            chip.className = 'ajax-chip';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.innerHTML = '&times;';
            btn.addEventListener('click', () => { selected.delete(key); renderChips(); renderHiddenInputs(); updateCount(); renderDropdown(); });
            chip.appendChild(document.createTextNode(item.text + ' '));
            chip.appendChild(btn);
            chipsEl.appendChild(chip);
        });
    }

    // ── Render hidden inputs ──────────────────────────────────
    function renderHiddenInputs() {
        hiddenEl.innerHTML = '';
        selected.forEach((item, key) => {
            const inp = document.createElement('input');
            inp.type  = 'hidden';
            inp.name  = CONFIG.inputName;
            inp.value = key;
            hiddenEl.appendChild(inp);
        });
    }

    // ── Update count ──────────────────────────────────────────
    function updateCount() {
        countEl.textContent = `${selected.size} ${CONFIG.label} dipilih`;
    }

    // ── Util ──────────────────────────────────────────────────
    function escHtml(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>