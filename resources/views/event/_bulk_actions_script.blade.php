@if($canEdit)
@php
    $jsPasalPelanggaran = $pasalPelanggaranOptions->map(function($p) {
        $poin = $p->skormax ?: $p->skormin ?: 0;
        return ['idpasal' => $p->idpasal, 'pasal' => $p->pasal, 'poin' => (int)$poin];
    })->values()->toArray();

    $jsPasalPenghargaan = $pasalPenghargaanOptions->map(function($p) {
        $poin = $p->skormax ?: $p->skormin ?: 0;
        return ['idpasal' => $p->idpasal, 'pasal' => $p->pasal, 'poin' => (int)$poin];
    })->values()->toArray();
@endphp
<script>
(function () {
    'use strict';

    /* ─── Data pasal dari server ─────────────────────────────────── */
    const PASAL_PELANGGARAN = {!! json_encode($jsPasalPelanggaran) !!};
    const PASAL_PENGHARGAAN = {!! json_encode($jsPasalPenghargaan) !!};
    const EVENT_SELESAI     = {{ $eventSelesai ? 'true' : 'false' }};
    const CSRF              = document.querySelector('meta[name="csrf-token"]')
                                ? document.querySelector('meta[name="csrf-token"]').content
                                : '{{ csrf_token() }}';

    /* ─── Elemen DOM ─────────────────────────────────────────────── */
    function $id(id) { return document.getElementById(id); }

    var selectAllCb        = $id('selectAllRows');
    var selectedCountEl    = $id('selectedCount');
    var bulkDeletePointBtn = $id('bulkDeletePointsBtn');
    var bulkEditStatusBtn  = $id('bulkEditStatusBtn');

    /* Modal edit status */
    var editModal          = $id('editStatusModal');
    var closeModalBtn      = $id('closeEditModal');
    var cancelModalBtn     = $id('cancelEditModal');
    var confirmEditBtn     = $id('confirmEditStatus');
    var statusHadirRb      = $id('statusHadir');
    var statusTidakHadirRb = $id('statusTidakHadir');
    var pasalSelect        = $id('pasalSelect');
    var autoActionInfo     = $id('autoActionInfo');
    var autoActionText     = $id('autoActionText');
    var modalSiswaList     = $id('modalSiswaList');
    var modalSiswaCount    = $id('modalSiswaCount');

    /* Forms */
    var bulkDeleteForm    = $id('bulkDeletePointsForm');
    var bulkDeleteInputs  = $id('bulkDeleteInputs');
    var bulkUpdateForm    = $id('bulkUpdateStatusForm');
    var bulkStatusInputs  = $id('bulkStatusInputs');

    /* ─── Time-gate guard ────────────────────────────────────────── */
    if (!EVENT_SELESAI) {
        /* Event belum selesai — tidak ada JS bulk yang berjalan */
        return;
    }

    /* ─── State ──────────────────────────────────────────────────── */
    var selectedRows = {}; /* { siswaId: true } */

    /* ─── Helpers ────────────────────────────────────────────────── */
    function getRowData(tr) {
        var pelStr = tr.getAttribute('data-pelanggaran-ids') || '';
        var penStr = tr.getAttribute('data-penghargaan-ids') || '';
        return {
            siswaId       : tr.getAttribute('data-siswa-id'),
            nama          : tr.getAttribute('data-nama') || 'Siswa',
            hadir         : tr.getAttribute('data-hadir') === '1',
            pelanggaranIds: pelStr ? pelStr.split(',').filter(function(x){ return x; }) : [],
            penghargaanIds: penStr ? penStr.split(',').filter(function(x){ return x; }) : [],
        };
    }

    function countSelected() {
        return Object.keys(selectedRows).length;
    }

    function refreshUI() {
        var count = countSelected();
        if (selectedCountEl) {
            selectedCountEl.textContent = count > 0 ? count + ' dipilih' : '';
        }

        /* Cek apakah ada poin pada baris yang terpilih */
        var hasPoin = false;
        var rows = document.querySelectorAll('.attendance-row');
        for (var i = 0; i < rows.length; i++) {
            var tr = rows[i];
            if (!selectedRows[tr.getAttribute('data-siswa-id')]) continue;
            var d = getRowData(tr);
            if (d.pelanggaranIds.length > 0 || d.penghargaanIds.length > 0) {
                hasPoin = true;
                break;
            }
        }

        if (bulkDeletePointBtn) bulkDeletePointBtn.disabled = count === 0 || !hasPoin;
        if (bulkEditStatusBtn)  bulkEditStatusBtn.disabled  = count === 0;

        /* Update selectAll state */
        var allRows = document.querySelectorAll('.attendance-row');
        if (selectAllCb && allRows.length > 0) {
            var allChecked = true;
            for (var j = 0; j < allRows.length; j++) {
                if (!selectedRows[allRows[j].getAttribute('data-siswa-id')]) {
                    allChecked = false;
                    break;
                }
            }
            selectAllCb.checked = allChecked;
        }
    }

    /* ─── Select All ─────────────────────────────────────────────── */
    if (selectAllCb) {
        selectAllCb.addEventListener('change', function () {
            var rows = document.querySelectorAll('.attendance-row');
            for (var i = 0; i < rows.length; i++) {
                var sid = rows[i].getAttribute('data-siswa-id');
                var cb  = rows[i].querySelector('.row-check');
                if (this.checked) {
                    selectedRows[sid] = true;
                    if (cb) cb.checked = true;
                } else {
                    delete selectedRows[sid];
                    if (cb) cb.checked = false;
                }
            }
            refreshUI();
        });
    }

    /* ─── Row Checkbox (delegated) ───────────────────────────────── */
    document.addEventListener('change', function (e) {
        var target = e.target;
        if (!target.classList.contains('row-check') && !target.classList.contains('row-check-mobile')) return;
        var tr = target.closest('[data-siswa-id]');
        if (!tr) return;
        var sid = tr.getAttribute('data-siswa-id');
        if (target.checked) selectedRows[sid] = true;
        else delete selectedRows[sid];
        refreshUI();
    });

    /* ─── Bulk Delete Points ─────────────────────────────────────── */
    if (bulkDeletePointBtn) {
        bulkDeletePointBtn.addEventListener('click', function () {
            if (countSelected() === 0) return;

            var pelIds = [], penIds = [];
            var rows = document.querySelectorAll('.attendance-row');
            for (var i = 0; i < rows.length; i++) {
                var tr = rows[i];
                if (!selectedRows[tr.getAttribute('data-siswa-id')]) continue;
                var d = getRowData(tr);
                pelIds = pelIds.concat(d.pelanggaranIds);
                penIds = penIds.concat(d.penghargaanIds);
            }

            if (pelIds.length === 0 && penIds.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak ada poin',
                    text: 'Siswa yang dipilih tidak memiliki poin event.',
                    confirmButtonColor: '#7c3aed'
                });
                return;
            }

            Swal.fire({
                icon: 'warning',
                title: 'Hapus Poin Event?',
                html: 'Akan menghapus <strong>' + pelIds.length + ' pelanggaran</strong> dan <strong>' + penIds.length + ' penghargaan</strong> beserta transaksinya.<br><br>Aksi ini tidak bisa dibatalkan.',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then(function(result) {
                if (!result.isConfirmed) return;

                /* Build form inputs */
                bulkDeleteInputs.innerHTML = '';
                for (var i = 0; i < pelIds.length; i++) {
                    var inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = 'pelanggaran_ids[]'; inp.value = pelIds[i];
                    bulkDeleteInputs.appendChild(inp);
                }
                for (var j = 0; j < penIds.length; j++) {
                    var inp2 = document.createElement('input');
                    inp2.type = 'hidden'; inp2.name = 'penghargaan_ids[]'; inp2.value = penIds[j];
                    bulkDeleteInputs.appendChild(inp2);
                }
                bulkDeleteForm.submit();
            });
        });
    }

    /* ─── Modal Edit Status ──────────────────────────────────────── */
    function escHtml(str) {
        return (str || '').replace(/[&<>"']/g, function(m) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
        });
    }

    function updatePasalDropdown(status) {
        pasalSelect.innerHTML = '<option value="">— Pilih pasal —</option>';
        var options = status === 'hadir' ? PASAL_PENGHARGAAN : PASAL_PELANGGARAN;
        for (var i = 0; i < options.length; i++) {
            var p = options[i];
            var opt = document.createElement('option');
            opt.value = p.idpasal;
            opt.textContent = p.idpasal + ' — ' + p.pasal + ' (' + p.poin + ' poin)';
            pasalSelect.appendChild(opt);
        }
        if (confirmEditBtn) confirmEditBtn.disabled = true;
        updateAutoActionInfo(status);
    }

    function updateAutoActionInfo(status) {
        if (!autoActionInfo || !autoActionText) return;
        if (status === 'hadir') {
            autoActionText.textContent = 'Pelanggaran event siswa terpilih akan dihapus → dibuat penghargaan sesuai pasal yang dipilih.';
            autoActionInfo.style.cssText = 'display:block;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;padding:10px 12px;font-size:.74rem;color:#166534;margin-bottom:14px';
        } else {
            autoActionText.textContent = 'Penghargaan event siswa terpilih akan dihapus → dibuat pelanggaran sesuai pasal yang dipilih.';
            autoActionInfo.style.cssText = 'display:block;background:#fff1f2;border:1px solid #fecdd3;border-radius:9px;padding:10px 12px;font-size:.74rem;color:#9f1239;margin-bottom:14px';
        }
    }

    function populateModalSiswaList() {
        if (!modalSiswaList) return;
        modalSiswaList.innerHTML = '';
        var count = 0;
        var rows = document.querySelectorAll('.attendance-row');
        for (var i = 0; i < rows.length; i++) {
            var tr = rows[i];
            if (!selectedRows[tr.getAttribute('data-siswa-id')]) continue;
            var d = getRowData(tr);
            var item = document.createElement('div');
            item.style.cssText = 'padding:8px 12px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;font-size:.78rem';
            var badge = d.hadir
                ? '<span style="font-size:.7rem;padding:2px 8px;border-radius:12px;font-weight:700;background:#dcfce7;color:#15803d">✓ Hadir</span>'
                : '<span style="font-size:.7rem;padding:2px 8px;border-radius:12px;font-weight:700;background:#fee2e2;color:#b91c1c">✗ Tidak Hadir</span>';
            item.innerHTML = '<span style="font-weight:600;color:#0f172a">' + escHtml(d.nama) + '</span>' + badge;
            modalSiswaList.appendChild(item);
            count++;
        }
        if (modalSiswaCount) modalSiswaCount.textContent = count;
        if (count === 0) {
            modalSiswaList.innerHTML = '<div style="padding:12px;color:#94a3b8;font-size:.78rem;text-align:center">Tidak ada siswa dipilih</div>';
        }
    }

    function closeModal() {
        if (editModal) editModal.style.display = 'none';
        document.body.style.overflow = '';
    }

    /* Buka modal */
    if (bulkEditStatusBtn) {
        bulkEditStatusBtn.addEventListener('click', function () {
            if (countSelected() === 0) return;
            /* Reset */
            if (statusHadirRb) statusHadirRb.checked = false;
            if (statusTidakHadirRb) statusTidakHadirRb.checked = false;
            if (pasalSelect) pasalSelect.innerHTML = '<option value="">— Pilih status terlebih dahulu —</option>';
            if (autoActionInfo) autoActionInfo.style.display = 'none';
            if (confirmEditBtn) confirmEditBtn.disabled = true;
            /* Highlight border reset */
            var labels = document.querySelectorAll('[name="newStatus"]');
            for (var i = 0; i < labels.length; i++) {
                var lbl = labels[i].closest('label');
                if (lbl) lbl.style.borderColor = '#e2e8f0';
            }
            populateModalSiswaList();
            editModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    }

    if (closeModalBtn)  closeModalBtn.addEventListener('click', closeModal);
    if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeModal);

    if (editModal) {
        editModal.addEventListener('click', function (e) {
            if (e.target === editModal) closeModal();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && editModal && editModal.style.display === 'flex') closeModal();
    });

    /* Radio change */
    function onRadioChange() {
        var status = null;
        if (statusHadirRb && statusHadirRb.checked) status = 'hadir';
        else if (statusTidakHadirRb && statusTidakHadirRb.checked) status = 'tidak_hadir';
        if (!status) return;
        updatePasalDropdown(status);
        /* Highlight border */
        var radios = document.querySelectorAll('[name="newStatus"]');
        for (var i = 0; i < radios.length; i++) {
            var lbl = radios[i].closest('label');
            if (lbl) {
                if (radios[i].checked) {
                    lbl.style.borderColor = radios[i].value === 'hadir' ? '#16a34a' : '#dc2626';
                } else {
                    lbl.style.borderColor = '#e2e8f0';
                }
            }
        }
    }

    if (statusHadirRb)      statusHadirRb.addEventListener('change', onRadioChange);
    if (statusTidakHadirRb) statusTidakHadirRb.addEventListener('change', onRadioChange);

    /* Pasal select change */
    if (pasalSelect) {
        pasalSelect.addEventListener('change', function () {
            var statusOk = (statusHadirRb && statusHadirRb.checked) || (statusTidakHadirRb && statusTidakHadirRb.checked);
            if (confirmEditBtn) confirmEditBtn.disabled = !this.value || !statusOk;
        });
    }

    /* Konfirmasi & submit */
    if (confirmEditBtn) {
        confirmEditBtn.addEventListener('click', function () {
            var statusVal = null;
            if (statusHadirRb && statusHadirRb.checked) statusVal = 'hadir';
            else if (statusTidakHadirRb && statusTidakHadirRb.checked) statusVal = 'tidak_hadir';
            var pasalVal = pasalSelect ? pasalSelect.value : '';
            if (!statusVal || !pasalVal) return;

            var count       = countSelected();
            var statusLabel = statusVal === 'hadir' ? 'Hadir' : 'Tidak Hadir';

            Swal.fire({
                icon: 'question',
                title: 'Konfirmasi Perubahan',
                html: 'Ubah status <strong>' + count + ' siswa</strong> menjadi <strong>' + statusLabel + '</strong>?<br>Poin event lama akan diganti sesuai pasal terpilih.',
                showCancelButton: true,
                confirmButtonColor: '#7c3aed',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal',
            }).then(function(result) {
                if (!result.isConfirmed) return;
                closeModal();

                bulkStatusInputs.innerHTML = '';
                var idx = 0;
                var ids = Object.keys(selectedRows);
                for (var i = 0; i < ids.length; i++) {
                    var addInput = function(name, val) {
                        var inp = document.createElement('input');
                        inp.type = 'hidden'; inp.name = name; inp.value = val;
                        bulkStatusInputs.appendChild(inp);
                    };
                    addInput('updates[' + idx + '][siswa_id]', ids[i]);
                    addInput('updates[' + idx + '][status]',   statusVal);
                    addInput('updates[' + idx + '][idpasal]',  pasalVal);
                    idx++;
                }
                bulkUpdateForm.submit();
            });
        });
    }

    refreshUI();

}());
</script>
@endif
