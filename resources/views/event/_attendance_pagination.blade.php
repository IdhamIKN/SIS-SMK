<script>
(function () {
    var PER_PAGE = 25;
    var currentPage = 1;

    var tableRows = [];
    var cardItems = [];

    function collectRows() {
        tableRows = Array.from(document.querySelectorAll('#attendanceTable tbody .attendance-row'));
        cardItems = Array.from(document.querySelectorAll('#cardList .evi'));
    }

    function totalPages() {
        return Math.max(1, Math.ceil(tableRows.length / PER_PAGE));
    }

    function applyPage(page) {
        currentPage = page;
        var start = (page - 1) * PER_PAGE;
        var end   = start + PER_PAGE;

        tableRows.forEach(function (row, i) {
            row.style.display = (i >= start && i < end) ? '' : 'none';
        });
        cardItems.forEach(function (card, i) {
            card.style.display = (i >= start && i < end) ? '' : 'none';
        });

        renderPaginationUI();
    }

    function renderPaginationUI() {
        var total  = tableRows.length;
        var pages  = totalPages();
        var paginationEl = document.getElementById('absenPagination');
        var infoEl       = document.getElementById('paginationInfo');
        var pageNumEl    = document.getElementById('pageNumbers');
        var prevBtn      = document.getElementById('prevPageBtn');
        var nextBtn      = document.getElementById('nextPageBtn');

        /* Sembunyikan bar jika data muat 1 halaman */
        if (paginationEl) {
            paginationEl.style.display = total <= PER_PAGE ? 'none' : 'flex';
        }
        if (total <= PER_PAGE) return;

        var start = Math.min((currentPage - 1) * PER_PAGE + 1, total);
        var end   = Math.min(currentPage * PER_PAGE, total);

        if (infoEl) {
            infoEl.textContent = 'Menampilkan ' + start + '–' + end + ' dari ' + total + ' siswa';
        }

        if (prevBtn) {
            prevBtn.disabled = currentPage <= 1;
            prevBtn.style.opacity = currentPage <= 1 ? '0.4' : '1';
            prevBtn.style.cursor  = currentPage <= 1 ? 'not-allowed' : 'pointer';
        }
        if (nextBtn) {
            nextBtn.disabled = currentPage >= pages;
            nextBtn.style.opacity = currentPage >= pages ? '0.4' : '1';
            nextBtn.style.cursor  = currentPage >= pages ? 'not-allowed' : 'pointer';
        }

        if (!pageNumEl) return;
        pageNumEl.innerHTML = '';
        var maxVisible = 5;
        var half       = Math.floor(maxVisible / 2);
        var rStart     = Math.max(1, currentPage - half);
        var rEnd       = Math.min(pages, rStart + maxVisible - 1);
        if (rEnd - rStart < maxVisible - 1) {
            rStart = Math.max(1, rEnd - maxVisible + 1);
        }

        if (rStart > 1) {
            pageNumEl.appendChild(makePageBtn(1));
            if (rStart > 2) pageNumEl.appendChild(makeDots());
        }
        for (var p = rStart; p <= rEnd; p++) {
            pageNumEl.appendChild(makePageBtn(p));
        }
        if (rEnd < pages) {
            if (rEnd < pages - 1) pageNumEl.appendChild(makeDots());
            pageNumEl.appendChild(makePageBtn(pages));
        }
    }

    function makeDots() {
        var s = document.createElement('span');
        s.textContent = '…';
        s.style.cssText = 'padding:0 4px;color:#94a3b8;font-size:.76rem;align-self:center';
        return s;
    }

    function makePageBtn(p) {
        var btn      = document.createElement('button');
        btn.textContent = p;
        var isActive = p === currentPage;
        btn.style.cssText =
            'min-width:32px;height:32px;border-radius:7px;border:1px solid ' +
            (isActive ? '#7c3aed' : '#e2e8f0') +
            ';background:' + (isActive ? '#7c3aed' : '#fff') +
            ';color:'      + (isActive ? '#fff'    : '#475569') +
            ';font-size:.76rem;font-weight:' + (isActive ? '800' : '600') +
            ';cursor:'     + (isActive ? 'default' : 'pointer') +
            ';transition:all .15s;padding:0 2px';
        if (!isActive) {
            btn.onmouseover = function () { this.style.background = '#f1f5f9'; };
            btn.onmouseout  = function () { this.style.background = '#fff'; };
        }
        btn.onclick = function () { absenGoToPage(p); };
        return btn;
    }

    /* ── Public API ─────────────────────────────────────────────── */
    window.absenChangePage = function (delta) {
        var next = Math.max(1, Math.min(totalPages(), currentPage + delta));
        if (next !== currentPage) absenGoToPage(next);
    };

    window.absenGoToPage = function (page) {
        applyPage(page);
        var anchor = document.getElementById('attendanceTable') ||
                     document.getElementById('cardList');
        if (anchor) {
            var top = anchor.getBoundingClientRect().top + window.scrollY - 80;
            window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
        }
    };

    /* ── Patch selectAll agar hanya memilih baris visible ────────── */
    function patchSelectAll() {
        var selectAllCb = document.getElementById('selectAllRows');
        if (!selectAllCb) return;

        /* Clone + replace untuk membuang listener lama dari _bulk_actions_script */
        var freshCb = selectAllCb.cloneNode(true);
        selectAllCb.parentNode.replaceChild(freshCb, selectAllCb);

        freshCb.addEventListener('change', function () {
            /* Hanya baris yang VISIBLE di halaman ini */
            var visibleRows = tableRows.filter(function (r) {
                return r.style.display !== 'none';
            });
            visibleRows.forEach(function (row) {
                var sid = row.getAttribute('data-siswa-id');
                var cb  = row.querySelector('.row-check');
                if (freshCb.checked) {
                    if (cb) cb.checked = true;
                    /* Picu event change agar selectedRows di bulk_script update */
                    if (cb) cb.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    if (cb) cb.checked = false;
                    if (cb) cb.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        });
    }

    /* ── Init ───────────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        collectRows();
        applyPage(1);
        patchSelectAll();
    });
}());
</script>
