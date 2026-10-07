<style>
    /* ═══════════════════════════════════════════════════════
           JURNAL MENGAJAR — RESPONSIVE STYLES
           Mengikuti pola /absen/rekap
           Breakpoints:
             xs  : < 480px
             sm  : 480–767px
             md  : 768–1023px
             lg  : 1024px+
        ═══════════════════════════════════════════════════════ */

    /* ── Wrapper ──────────────────────────────────────────── */
    .jurnal-wrap {
        padding: 0 12px;
        max-width: 1280px;
        margin: 0 auto;
        box-sizing: border-box;
    }

    @media (min-width: 768px) {
        .jurnal-wrap {
            padding: 0 20px;
        }
    }

    @media (min-width: 1024px) {
        .jurnal-wrap {
            padding: 0 28px;
        }
    }

    /* ── Filter section ───────────────────────────────────── */
    .filter-section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        margin-bottom: 16px;
        overflow: hidden;
    }

    .filter-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        cursor: pointer;
        user-select: none;
        background: #f1f5f9;
        border: none;
        width: 100%;
        font-family: inherit;
        font-size: .875rem;
        font-weight: 700;
        color: #0f172a;
        gap: 8px;
    }

    .filter-toggle .ft-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-toggle .ft-chevron {
        transition: transform .2s;
        color: #64748b;
        font-size: .8rem;
    }

    .filter-toggle.open .ft-chevron {
        transform: rotate(180deg);
    }

    @media (min-width: 768px) {
        .filter-toggle {
            display: none;
        }
    }

    .filter-body {
        padding: 12px 16px 16px;
        display: none;
    }

    .filter-body.open {
        display: block;
    }

    @media (min-width: 768px) {
        .filter-body {
            display: block !important;
            padding: 16px;
        }
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        align-items: end;
    }

    @media (max-width: 479px) {
        .filter-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (min-width: 768px) {
        .filter-grid {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }
    }

    .form-label {
        display: block;
        font-size: .8rem;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 5px;
    }

    .form-input {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: .875rem;
        font-family: inherit;
        color: #0f172a;
        background: #fff;
        box-sizing: border-box;
        -webkit-appearance: none;
        appearance: none;
    }

    .form-input:focus {
        outline: 2px solid #0ea5e9;
        outline-offset: -1px;
    }

    textarea.form-input {
        resize: vertical;
        line-height: 1.5;
    }

    .form-error {
        margin-top: 6px;
        color: #dc2626;
        font-size: .75rem;
        font-weight: 600;
    }

    .form-hint {
        margin-top: 6px;
        color: #64748b;
        font-size: .74rem;
        line-height: 1.4;
    }

    .form-group {
        margin-bottom: 14px;
    }

    .form-group.mb-0 {
        margin-bottom: 0;
    }

    /* ── Card header ──────────────────────────────────────── */
    .c-head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        flex-wrap: wrap;
    }

    @media (min-width: 768px) {
        .c-head {
            padding: 14px 18px;
        }
    }

    .c-head h3 {
        font-size: .9rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        flex: 1;
    }

    @media (min-width: 768px) {
        .c-head h3 {
            font-size: 1rem;
        }
    }

    .hbadge {
        font-size: .7rem;
        font-weight: 700;
        background: #f1f5f9;
        color: #475569;
        padding: 3px 10px;
        border-radius: 20px;
    }

    /* ── Tabel desktop ────────────────────────────────────── */
    .jurnal-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    @media (max-width: 767px) {
        .jurnal-table-wrap {
            display: none;
        }
    }

    .jurnal-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .78rem;
        background: #fff;
    }

    .jurnal-table thead tr {
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
    }

    .jurnal-table th {
        padding: 11px 10px;
        text-align: left;
        font-size: .68rem;
        font-weight: 700;
        color: #64748b;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .jurnal-table td {
        padding: 10px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .jurnal-table tbody tr:last-child td {
        border-bottom: none;
    }

    .jurnal-table tbody tr:hover td {
        background: #fafbfc;
    }

    .jadwal-cell,
    .guru-cell {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .jadwal-cell strong,
    .guru-cell strong {
        font-weight: 700;
        color: #0f172a;
    }

    .jadwal-cell small,
    .guru-cell small {
        font-size: .7rem;
        color: #64748b;
    }

    /* ── Card list mobile ─────────────────────────────────── */
    .jurnal-card-list {
        display: none;
    }

    @media (max-width: 767px) {
        .jurnal-card-list {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
    }

    .jci {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        background: #fff;
    }

    .jci:last-child {
        border-bottom: none;
    }

    .jci:active {
        background: #f8fafc;
    }

    .jci-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 4px;
    }

    .jci-title {
        font-weight: 700;
        font-size: .88rem;
        color: #0f172a;
        flex: 1;
        min-width: 0;
    }

    .jci-tgl {
        font-size: .72rem;
        color: #64748b;
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .jci-sub {
        font-size: .72rem;
        color: #64748b;
        margin-bottom: 6px;
    }

    .jci-mid {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 7px;
    }

    .jci-chip {
        font-size: .7rem;
        color: #64748b;
        background: #f1f5f9;
        border-radius: 5px;
        padding: 2px 7px;
        font-weight: 600;
    }

    .jci-bot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 6px;
    }

    .jci-presence {
        display: flex;
        gap: 5px;
        align-items: center;
    }

    .jci-actions {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
    }

    /* ── Presence chips ───────────────────────────────────── */
    .presence-chip {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: .68rem;
        font-weight: 700;
    }

    .presence-chip.ok {
        background: #dcfce7;
        color: #166534;
    }

    .presence-chip.danger {
        background: #fee2e2;
        color: #991b1b;
    }

    /* ── Row actions ──────────────────────────────────────── */
    .row-actions {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .row-actions form {
        margin: 0;
    }

    .icon-action {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #0369a1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        text-decoration: none;
        font-size: .82rem;
    }

    .icon-action:hover {
        background: #eff6ff;
    }

    .icon-action.danger {
        color: #dc2626;
    }

    .icon-action.danger:hover {
        background: #fee2e2;
    }

    .bukti-preview-btn {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #eff6ff;
        color: #0369a1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: .82rem;
    }

    .bukti-preview-btn:hover {
        background: #dbeafe;
    }

    /* ── Tombol aksi inline (terapkan filter, dsb) ───────── */
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 7px;
        font-size: .72rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        font-family: inherit;
        text-decoration: none;
        transition: opacity .15s;
        white-space: nowrap;
    }

    .action-btn:active {
        opacity: .75;
    }

    .btn-view {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .btn-edit {
        background: #f0fdf4;
        color: #15803d;
    }

    /* ── Empty state ──────────────────────────────────────── */
    .jurnal-empty {
        text-align: center;
        padding: 36px 20px;
        color: #64748b;
    }

    .jurnal-empty i {
        font-size: 2.5rem;
        opacity: .3;
        display: block;
        margin-bottom: 10px;
    }

    .jurnal-empty strong {
        display: block;
        color: #0f172a;
        margin-bottom: 4px;
        font-size: .9rem;
    }

    /* ── Action bar (fixed bottom) ────────────────────────── */
    .action-bar {
        position: fixed;
        bottom: var(--footer-h, 0);
        left: 0;
        right: 0;
        padding: 10px 12px 12px;
        background: rgba(255, 255, 255, .96);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-top: 1px solid #e2e8f0;
        display: flex;
        gap: 8px;
        z-index: 999;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, .06);
    }

    @media (min-width: 768px) {
        .action-bar {
            padding: 10px 24px 12px;
            gap: 12px;
            justify-content: flex-end;
        }
    }

    .ab-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 12px 14px;
        border-radius: 12px;
        font-size: .82rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        transition: all .18s;
        line-height: 1;
        white-space: nowrap;
    }

    @media (min-width: 768px) {
        .ab-btn {
            flex: unset;
            min-width: 130px;
            font-size: .875rem;
            padding: 12px 20px;
        }
    }

    .ab-btn:active {
        transform: scale(.97);
    }

    .ab-btn-back {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .ab-btn-back:hover {
        background: #e2e8f0;
    }

    .ab-btn-primary {
        background: var(--event-primary, #0ea5e9);
        color: #fff;
        box-shadow: 0 3px 12px rgba(14, 165, 233, .3);
    }

    .ab-btn-primary:hover {
        filter: brightness(1.08);
    }

    .ab-btn-primary:disabled {
        opacity: .55;
        cursor: not-allowed;
        filter: none;
    }

    /* ── Pagination ───────────────────────────────────────── */
    .jurnal-pagination {
        padding: 12px 14px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 5px;
    }

    .pg-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: .78rem;
        font-weight: 700;
        text-decoration: none;
        font-family: inherit;
        border: 1px solid #e2e8f0;
        background: #f1f5f9;
        color: #475569;
        cursor: pointer;
        transition: background .15s;
        white-space: nowrap;
    }

    .pg-btn:hover {
        background: #e2e8f0;
    }

    .pg-btn.active {
        background: var(--event-primary, #0ea5e9);
        color: #fff;
        border-color: transparent;
        pointer-events: none;
    }

    .pg-btn.disabled {
        opacity: .4;
        cursor: not-allowed;
        pointer-events: none;
    }

    @media (max-width: 479px) {
        .pg-num {
            display: none;
        }

        .pg-num.active {
            display: inline-flex;
        }
    }

    /* ── Form — schedule summary ──────────────────────────── */
    .schedule-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 10px;
        border-top: 1px solid #e2e8f0;
        padding: 14px 16px;
        background: #f8fafc;
    }

    .schedule-summary > div {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        padding: 10px 12px;
    }

    .summary-label {
        display: block;
        color: #64748b;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .schedule-summary strong {
        display: block;
        color: #0f172a;
        font-size: .9rem;
        line-height: 1.25;
    }

    /* ── Form — counter grid ──────────────────────────────── */
    .counter-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }

    @media (max-width: 480px) {
        .counter-grid {
            grid-template-columns: 1fr;
        }
    }

    .counter-box {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        padding: 10px 12px;
    }

    .counter-box span {
        display: block;
        color: #64748b;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .counter-box strong {
        display: block;
        color: #0f172a;
        font-size: 1.45rem;
        line-height: 1.2;
    }

    .counter-box.danger strong {
        color: #dc2626;
    }

    /* ── Form — upload grid ───────────────────────────────── */
    .upload-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    .upload-choice {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px;
        border: 1px dashed #94a3b8;
        border-radius: 8px;
        background: #f8fafc;
        color: #0f172a;
        cursor: pointer;
        min-height: 64px;
        margin: 0;
    }

    .upload-choice i {
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #e0f2fe;
        color: #0369a1;
        flex: 0 0 auto;
    }

    .upload-choice span {
        font-size: .84rem;
        font-weight: 700;
    }

    .upload-choice input {
        display: none;
    }

    .existing-file {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        padding: 8px 10px;
        border-radius: 8px;
        background: #f1f5f9;
        font-size: .8rem;
        font-weight: 700;
        color: #0369a1;
    }

    .file-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #0369a1;
        font-weight: 700;
        background: none;
        border: none;
        cursor: pointer;
        font-size: .8rem;
        font-family: inherit;
        padding: 0;
    }

    /* ── Bukti modal overlay ──────────────────────────────── */
    .bukti-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, .62);
        z-index: 30000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
    }

    .bukti-modal-overlay.open {
        display: flex !important;
    }

    .bukti-modal {
        width: 100%;
        max-width: 800px;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 12px 40px rgba(0, 0, 0, .25);
    }

    .bukti-modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 14px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .bukti-modal-title {
        font-weight: 700;
        color: #0f172a;
        font-size: .9rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .bukti-modal-close {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
    }

    .bukti-modal-close:hover {
        background: #f1f5f9;
    }

    .bukti-modal-body {
        padding: 14px;
        background: #fff;
    }

    .bukti-modal-media {
        width: 100%;
        max-height: 70vh;
        object-fit: contain;
        border-radius: 8px;
        display: block;
        margin: 0 auto;
        background: #f1f5f9;
    }

    /* ── Form grid ────────────────────────────────────────── */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 12px;
        padding: 14px 16px;
    }

    @media (max-width: 479px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    .c-body {
        padding: 14px 16px;
    }
</style>
