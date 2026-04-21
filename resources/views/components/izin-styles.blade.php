<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

    :root {
        --purple-primary: #9333ea;
        --purple-dark: #7c3aed;
        --purple-fade: #f3e8ff;
        --surface: #f1f5f9;
        --card: #ffffff;
        --border: #e2e8f0;
        --text-main: #0f172a;
        --text-muted: #64748b;
        --footer-h: 64px;
    }

    .izin-wrap {
        max-width: 520px;
        margin: 0 auto;
        padding: 14px 14px 100px;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* Page strip */
    .page-strip-izin {
        background: linear-gradient(135deg, #9333ea 0%, #a855f7 100%);
    }

    .page-strip-approved {
        background: linear-gradient(135deg, #16a34a, #22c55e);
    }

    .page-strip-rejected {
        background: linear-gradient(135deg, #ef4444, #f87171);
    }

    .page-strip {
        border-radius: 16px;
        padding: 16px 18px;
        margin-bottom: 14px;
        position: relative;
        overflow: hidden;
    }

    .page-strip::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
        background-size: 60px 60px;
    }

    .live-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, .2);
        border: 1px solid rgba(255, 255, 255, .3);
        border-radius: 20px;
        padding: 3px 10px;
        font-size: .7rem;
        color: #fff;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .live-dot {
        width: 6px;
        height: 6px;
        background: #d8b4fe;
        border-radius: 50%;
        animation: blink 1.5s ease infinite;
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 1;
            transform: scale(1);
        }

        50% {
            opacity: .5;
            transform: scale(1.3);
        }
    }

    .page-strip h2 {
        font-size: 1.15rem;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .page-strip p {
        font-size: .77rem;
        color: rgba(255, 255, 255, .8);
        margin-top: 3px;
    }

    /* Steps */
    .steps {
        display: flex;
        margin-bottom: 14px;
    }

    .step {
        flex: 1;
        text-align: center;
        position: relative;
    }

    .step::after {
        content: '';
        position: absolute;
        top: 13px;
        left: 50%;
        width: 100%;
        height: 2px;
        background: var(--border);
    }

    .step:last-child::after {
        display: none;
    }

    .step.done::after {
        background: var(--purple-primary);
    }

    .step-dot {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: var(--border);
        color: var(--text-muted);
        font-size: .68rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 4px;
        position: relative;
        z-index: 1;
        transition: all .3s;
    }

    .step.active .step-dot {
        background: var(--purple-primary);
        color: #fff;
    }

    .step.done .step-dot {
        background: var(--purple-primary);
        color: #fff;
    }

    .step-lbl {
        font-size: .6rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .step.active .step-lbl,
    .step.done .step-lbl {
        color: var(--text-main);
    }

    /* Status chips */
    .status-bar {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 14px;
    }

    .s-chip {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 10px 12px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .05);
    }

    .ci {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
        background: #f1f5f9;
    }

    .ci-p {
        background: var(--purple-fade);
    }

    .ci-g {
        background: #dcfce7;
    }

    .ci-r {
        background: #fee2e2;
    }

    .c-lbl {
        font-size: .65rem;
        color: var(--text-muted);
    }

    .c-val {
        font-size: .8rem;
        font-weight: 600;
        margin-top: 1px;
    }

    /* Cards */
    .card {
        background: var(--card);
        border-radius: 14px;
        border: 1px solid var(--border);
        margin-bottom: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .05), 0 4px 14px rgba(0, 0, 0, .03);
    }

    .c-head {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .c-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .9rem;
    }

    .c-head h3 {
        font-size: .87rem;
        font-weight: 600;
        color: var(--text-main);
        margin: 0;
    }

    .hbadge {
        margin-left: auto;
        font-size: .68rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        background: #f1f5f9;
        color: #64748b;
    }

    /* Alerts */
    .alert {
        border-radius: 10px;
        padding: 11px 14px;
        font-size: .82rem;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 12px;
    }

    .a-ok {
        background: #f0fdf4;
        border: 1px solid #86efac;
        color: #15803d;
    }

    .a-warn {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        color: #b45309;
    }

    .a-pending {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        color: #92400e;
    }

    .a-approved {
        background: #f0fdf4;
        border: 1px solid #86efac;
        color: #15803d;
    }

    .a-rejected {
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: #dc2626;
    }

    /* Buttons */
    .btn-sub {
        width: 100%;
        padding: 14px;
        border-radius: 12px;
        background: var(--purple-primary);
        color: #fff;
        font-size: .95rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(147, 51, 234, .35);
        transition: all .2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-sub:hover:not(:disabled) {
        background: var(--purple-dark);
        transform: translateY(-1px);
    }

    .btn-sub:disabled {
        background: #94a3b8;
        cursor: not-allowed;
    }

    .btn-izin {
        padding: 9px 18px;
        border-radius: 9px;
        font-size: .82rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all .2s;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .btn-izin-primary {
        background: var(--purple-primary);
        color: #fff;
    }

    .btn-izin-primary:hover {
        background: var(--purple-dark);
    }

    .btn-izin-secondary {
        background: transparent;
        color: var(--text-muted);
        border: 1px solid var(--border);
    }

    .btn-izin-secondary:hover {
        border-color: var(--purple-primary);
        color: var(--purple-primary);
    }

    /* List items */
    .izin-item {
        transition: all .2s;
    }

    .izin-item:hover {
        transform: translateY(-2px);
    }

    .badge-status {
        font-size: .65rem;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: 600;
    }

    .status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .status-approved {
        background: #f0fdf4;
        color: #15803d;
    }

    .status-rejected {
        background: #fef2f2;
        color: #dc2626;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }

    .empty-icon {
        font-size: 4rem;
        color: var(--purple-fade);
        margin-bottom: 16px;
    }

    .empty-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 8px;
    }

    .empty-text {
        color: var(--text-muted);
        margin-bottom: 24px;
    }

    /* Pagination */
    .pagination-chips {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-top: 24px;
    }

    .page-chip {
        padding: 8px 16px;
        border-radius: 20px;
        background: var(--card);
        border: 1px solid var(--border);
        font-size: .8rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        transition: all .2s;
    }

    .page-chip.active,
    .page-chip:hover {
        background: var(--purple-primary);
        color: #fff;
        border-color: var(--purple-primary);
    }

    /* ── FIXED ACTION BAR ────────────────────────────────── */
    .action-bar {
        position: fixed;
        bottom: var(--footer-h);
        left: 0; right: 0;
        padding: 10px 16px 12px;
        background: rgba(255,255,255,.96);
        backdrop-filter: blur(10px);
        border-top: 1px solid var(--border);
        display: flex;
        gap: 10px;
        z-index: 999;
        box-shadow: 0 -4px 20px rgba(0,0,0,.06);
    }
    .action-bar form { flex: 1; display: flex; }

    /* ── TOMBOL UNIVERSAL (dipakai action-bar & SweetAlert) ── */
    .ab-btn {
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: .875rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        transition: all .18s;
        line-height: 1;
        white-space: nowrap;
    }
    .ab-btn:active { transform: scale(0.97); }

    /* Kembali / Batal — abu */
    .ab-btn-back {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid var(--border);
    }
    .ab-btn-back:hover { background: #e2e8f0; color: #334155; }

    /* Edit — ungu */
    .ab-btn-edit {
        background: var(--purple-primary);
        color: #fff !important;
        box-shadow: 0 3px 12px rgba(124,58,237,.3);
    }
    .ab-btn-edit:hover { filter: brightness(1.08); }

    /* Hapus / Konfirmasi — merah */
    .ab-btn-delete {
        background: #ef4444;
        color: #fff !important;
        box-shadow: 0 3px 12px rgba(239,68,68,.25);
    }
    .ab-btn-delete:hover { background: #dc2626; }

    /* ── SWEETALERT OVERRIDE ──────────────────────────────── */
    .swal2-popup {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        border-radius: 18px !important;
        padding: 28px 24px 24px !important;
    }
    .swal2-title  { font-size: 1.1rem !important; font-weight: 700 !important; }
    .swal2-html-container { font-size: .875rem !important; }
    .swal2-actions {
        gap: 10px !important;
        margin-top: 20px !important;
        width: 100% !important;
        padding: 0 !important;
    }
    .swal2-actions .ab-btn {
        flex: 1;
        margin: 0 !important;
    }

    /* ── DETAIL ROW ──────────────────────────────────────── */
    .detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 7px 0;
        font-size: .85rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-row .dr-label { color: var(--text-muted, #64748b); }
    .detail-row .dr-value { font-weight: 600; color: var(--text-main, #0f172a); text-align: right; }

    /* ── AVATAR ──────────────────────────────────────────── */
    .verifier-avatar {
        width: 40px; height: 40px; border-radius: 50%;
        background: var(--purple-fade, #ede9fe);
        display: flex; align-items: center; justify-content: center;
        font-size: 1rem; color: var(--purple-dark, #5b21b6); flex-shrink: 0;
    }
    .siswa-avatar {
        width: 46px; height: 46px; border-radius: 50%;
        background: var(--purple-fade, #ede9fe);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; font-weight: 700;
        color: var(--purple-dark, #5b21b6); flex-shrink: 0;
        border: 2px solid var(--border, #e2e8f0);
    }

    /* ── ACTION BUTTON GROUP ─────────────────────────────── */
    .action-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 8px;
        font-size: .78rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        transition: all .18s;
        white-space: nowrap;
        line-height: 1;
    }
    .action-btn:active { transform: scale(0.96); }

    /* Lihat — biru */
    .btn-view {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }
    .btn-view:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }

    /* Edit — amber/oranye */
    .btn-edit {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .btn-edit:hover {
        background: #fef3c7;
        border-color: #fcd34d;
    }

    /* Hapus — merah */
    .btn-delete {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
    .btn-delete:hover {
        background: #fee2e2;
        border-color: #fca5a5;
    }

    /* ── IZIN CARD ───────────────────────────────────────── */
    .izin-item .c-head {
        align-items: flex-start;
    }
    .izin-item .c-head h3 {
        flex: 1;
    }
    .izin-item .izin-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: .78rem;
        margin-top: 8px;
        flex-wrap: wrap;
        gap: 6px;
    }
    .izin-item .izin-meta .meta-date {
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .izin-item .izin-meta .meta-verifier {
        color: var(--purple-primary, #7c3aed);
        font-weight: 600;
        font-size: .75rem;
    }
    .izin-item .izin-meta .meta-bukti {
        font-size: .72rem;
        color: var(--text-muted);
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 2px 8px;
    }

    /* ── STATUS BADGE TWEAK ──────────────────────────────── */
    .status-approved { background: #dcfce7 !important; color: #15803d !important; }
    .status-rejected { background: #fee2e2 !important; color: #dc2626 !important; }
    .status-pending  { background: #fef9c3 !important; color: #b45309 !important; }

    /* ── FLOATING BUTTON — tidak mepet footer ────────────── */
    .fab-add {
        position: fixed;
        bottom: calc(var(--footer-h) + 16px);
        right: 20px;
        width: auto;
        height: 46px;
        padding: 0 18px;
        border-radius: 23px;
        background: var(--purple-primary, #7c3aed);
        color: #fff;
        font-size: .85rem;
        font-weight: 700;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        text-decoration: none;
        box-shadow: 0 6px 20px rgba(124, 58, 237, .4);
        z-index: 1000;
        transition: all .2s;
        border: none;
        cursor: pointer;
    }
    .fab-add:hover {
        background: #6d28d9;
        box-shadow: 0 8px 24px rgba(124, 58, 237, .5);
        transform: translateY(-1px);
    }
    .fab-add:active { transform: scale(0.97); }
</style>
