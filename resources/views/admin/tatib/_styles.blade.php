<style>
    .tatib-wrap {
        padding: 0 14px calc(var(--footer-h, 64px) + 84px);
    }

    .tatib-grid {
        display: grid;
        gap: 10px;
    }

    .tatib-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 12px;
    }

    .tatib-stat {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 10px;
        padding: 12px;
        min-width: 0;
        box-shadow: 0 1px 5px rgba(15, 23, 42, .05);
    }

    .tatib-stat .lbl {
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
    }

    .tatib-stat .val {
        color: #0f172a;
        font-size: 1.15rem;
        font-weight: 800;
        margin-top: 2px;
    }

    .tatib-filter,
    .tatib-card,
    .tatib-form-card {
        background: #fff;
        border: 1px solid #eef2f7;
        border-radius: 10px;
        box-shadow: 0 1px 5px rgba(15, 23, 42, .05);
    }

    .tatib-filter {
        padding: 12px;
        display: grid;
        grid-template-columns: 1.4fr 1fr 1fr .7fr auto;
        gap: 9px;
        align-items: end;
        margin-bottom: 12px;
    }

    .tatib-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
        min-width: 0;
    }

    .tatib-field label {
        color: #475569;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .tatib-input,
    .tatib-select,
    .tatib-textarea {
        width: 100%;
        border: 1px solid #dbe3ef;
        border-radius: 8px;
        background: #f8fafc;
        padding: 9px 11px;
        color: #0f172a;
        font-size: .82rem;
        outline: none;
    }

    .tatib-input:focus,
    .tatib-select:focus,
    .tatib-textarea:focus {
        border-color: #2563eb;
        background: #fff;
    }

    .tatib-textarea {
        min-height: 96px;
        resize: vertical;
    }

    .tatib-check {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 38px;
        color: #0f172a;
        font-size: .82rem;
        font-weight: 800;
    }

    .tatib-check input {
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
    }

    .tatib-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .tatib-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border: 0;
        border-radius: 8px;
        padding: 9px 12px;
        font-size: .8rem;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        min-height: 38px;
    }

    .tatib-btn-primary {
        background: #2563eb;
        color: #fff;
    }

    .tatib-btn-green {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .tatib-btn-soft {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }

    .tatib-btn-warn {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }

    .tatib-btn-danger {
        background: #fff1f2;
        color: #be123c;
        border: 1px solid #fecdd3;
    }

    .tatib-list {
        display: grid;
        gap: 9px;
    }

    .tatib-card {
        padding: 13px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: start;
    }

    .tatib-title {
        color: #0f172a;
        font-size: .92rem;
        font-weight: 800;
        margin: 0 0 5px;
    }

    .tatib-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        color: #64748b;
        font-size: .74rem;
    }

    .tatib-meta span,
    .tatib-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 999px;
        padding: 3px 8px;
    }

    .tatib-body {
        color: #334155;
        font-size: .82rem;
        margin: 9px 0 0;
        line-height: 1.45;
    }

    .tatib-pill-red {
        background: #fff1f2;
        color: #be123c;
        border-color: #fecdd3;
    }

    .tatib-pill-green {
        background: #ecfdf5;
        color: #047857;
        border-color: #bbf7d0;
    }

    .tatib-pill-blue {
        background: #eff6ff;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .tatib-pill-amber {
        background: #fffbeb;
        color: #b45309;
        border-color: #fde68a;
    }

    .tatib-pagination {
        margin-top: 14px;
    }

    .tatib-form-card {
        padding: 14px;
        margin-bottom: 12px;
    }

    .tatib-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .tatib-form-grid .full {
        grid-column: 1 / -1;
    }

    .tatib-error {
        color: #be123c;
        font-size: .73rem;
        font-weight: 700;
    }

    .tatib-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
    }

    .tatib-table th,
    .tatib-table td {
        padding: 10px;
        border-bottom: 1px solid #eef2f7;
        font-size: .8rem;
        vertical-align: top;
    }

    .tatib-table th {
        color: #475569;
        background: #f8fafc;
        font-size: .72rem;
        text-transform: uppercase;
    }

    @media (max-width: 900px) {
        .tatib-filter {
            grid-template-columns: 1fr 1fr;
        }

        .tatib-stats {
            grid-template-columns: 1fr;
        }

        .tatib-card {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {

        .tatib-filter,
        .tatib-form-grid {
            grid-template-columns: 1fr;
        }

        .tatib-actions {
            width: 100%;
        }

        .tatib-btn {
            flex: 1;
        }
    }
</style>
