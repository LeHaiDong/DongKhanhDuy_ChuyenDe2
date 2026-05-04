<style>
    .seller-shell {
        width: min(1180px, calc(100% - 32px));
        margin: 0 auto;
        padding: 34px 0 70px;
    }

    .seller-hero,
    .seller-card {
        border: 1px solid #e2e8f0;
        border-radius: 28px;
        background: white;
        box-shadow: 0 22px 55px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .seller-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
        gap: 24px;
        margin-bottom: 24px;
        padding: 34px;
        background:
            radial-gradient(circle at 82% 18%, rgba(255, 255, 255, 0.28), transparent 22%),
            linear-gradient(135deg, #172554 0%, #2563eb 48%, #0f172a 100%);
        color: white;
    }

    .seller-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        width: fit-content;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .seller-hero h1 {
        margin: 18px 0 14px;
        font-size: clamp(34px, 5vw, 58px);
        line-height: 0.98;
        letter-spacing: -0.06em;
    }

    .seller-hero p {
        max-width: 620px;
        margin: 0;
        color: rgba(255, 255, 255, 0.86);
        font-size: 16px;
    }

    .seller-steps {
        display: grid;
        gap: 12px;
    }

    .seller-step {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 16px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.12);
    }

    .seller-step strong {
        display: block;
        margin-bottom: 4px;
    }

    .seller-step span {
        color: rgba(255, 255, 255, 0.78);
        font-size: 13px;
    }

    .seller-card {
        padding: 28px;
        margin-bottom: 22px;
    }

    .seller-card-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .seller-card-head h2,
    .seller-card-head h1 {
        margin: 0;
        color: #0f172a;
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -0.05em;
    }

    .seller-card-head p {
        margin: 8px 0 0;
        color: #64748b;
    }

    .seller-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .seller-field {
        display: grid;
        gap: 8px;
    }

    .seller-field.full {
        grid-column: 1 / -1;
    }

    .seller-field label {
        color: #334155;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .seller-field input,
    .seller-field select,
    .seller-field textarea {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        background: #fff;
        color: #0f172a;
        padding: 13px 15px;
        font: inherit;
        outline: none;
    }

    .seller-field input:focus,
    .seller-field select:focus,
    .seller-field textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
    }

    .seller-help {
        color: #64748b;
        font-size: 12px;
        font-weight: 650;
        line-height: 1.5;
    }

    .seller-preview {
        width: 160px;
        height: 110px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        object-fit: cover;
        background: #f8fafc;
    }

    .seller-error {
        color: #dc2626;
        font-size: 12px;
        font-weight: 800;
    }

    .seller-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 22px;
    }

    .seller-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        border: 0;
        border-radius: 999px;
        padding: 12px 18px;
        color: white;
        background: #0f172a;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
    }

    .seller-btn.primary {
        background: linear-gradient(135deg, #2563eb, #06b6d4);
    }

    .seller-btn.outline {
        border: 1px solid #e2e8f0;
        background: white;
        color: #0f172a;
    }

    .seller-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .seller-stats-wide {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .seller-stat {
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        background: white;
        padding: 20px;
        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.06);
    }

    .seller-stat strong {
        display: block;
        color: #0f172a;
        font-size: 32px;
        line-height: 1;
    }

    .seller-stat span {
        display: block;
        margin-top: 8px;
        color: #64748b;
        font-weight: 750;
    }

    .seller-stat.highlight {
        border-color: rgba(37, 99, 235, 0.35);
        background:
            radial-gradient(circle at top right, rgba(37, 99, 235, 0.16), transparent 34%),
            #ffffff;
    }

    .seller-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 20px;
        padding: 34px;
        text-align: center;
        color: #64748b;
        background: #f8fafc;
    }

    .seller-table-wrap {
        overflow-x: auto;
    }

    .seller-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
    }

    .seller-table th,
    .seller-table td {
        border-bottom: 1px solid #edf2f7;
        padding: 14px;
        text-align: left;
        vertical-align: middle;
    }

    .seller-table th {
        color: #64748b;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .seller-product-thumb {
        width: 62px;
        height: 62px;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        object-fit: contain;
        background: #fff;
    }

    .seller-status {
        display: inline-flex;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 900;
        background: #dcfce7;
        color: #166534;
    }

    .seller-status.muted {
        background: #fee2e2;
        color: #991b1b;
    }

    .seller-status.pending {
        background: #fef3c7;
        color: #92400e;
    }

    @media (max-width: 860px) {
        .seller-hero,
        .seller-grid,
        .seller-stats {
            grid-template-columns: 1fr;
        }
    }
</style>
