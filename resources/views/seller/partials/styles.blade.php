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

    .seller-dashboard {
        display: grid;
        gap: 22px;
    }

    .seller-dashboard .seller-card {
        margin-bottom: 0;
    }

    .seller-dashboard-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(360px, 0.85fr);
        gap: 22px;
        align-items: stretch;
    }

    .seller-hero-copy,
    .seller-chart-card,
    .seller-stat-card {
        border: 1px solid rgba(125, 211, 252, 0.45);
        box-shadow: 0 26px 70px rgba(15, 23, 42, 0.1);
    }

    .seller-hero-copy {
        position: relative;
        overflow: hidden;
        border-radius: 32px;
        padding: 34px;
        color: white;
        background:
            radial-gradient(circle at 86% 18%, rgba(255, 255, 255, 0.28), transparent 20%),
            radial-gradient(circle at 10% 100%, rgba(34, 211, 238, 0.32), transparent 34%),
            linear-gradient(135deg, #082f49 0%, #1d4ed8 50%, #0f766e 100%);
    }

    .seller-hero-copy h1 {
        max-width: 760px;
        margin: 18px 0 14px;
        font-size: clamp(36px, 5vw, 60px);
        line-height: 0.95;
        letter-spacing: -0.07em;
    }

    .seller-hero-copy p {
        max-width: 680px;
        margin: 0;
        color: rgba(255, 255, 255, 0.84);
        font-size: 16px;
        line-height: 1.7;
    }

    .seller-hero-actions {
        margin-top: 24px;
    }

    .seller-btn.outline.light {
        border-color: rgba(255, 255, 255, 0.34);
        background: rgba(255, 255, 255, 0.14);
        color: white;
        backdrop-filter: blur(14px);
    }

    .seller-hero-mini {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 28px;
    }

    .seller-hero-mini div {
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 22px;
        padding: 16px;
        background: rgba(255, 255, 255, 0.13);
        backdrop-filter: blur(12px);
    }

    .seller-hero-mini strong,
    .seller-hero-mini span {
        display: block;
    }

    .seller-hero-mini strong {
        font-size: 22px;
        letter-spacing: -0.04em;
    }

    .seller-hero-mini span {
        margin-top: 6px;
        color: rgba(255, 255, 255, 0.76);
        font-size: 12px;
        font-weight: 800;
    }

    .seller-chart-card {
        border-radius: 32px;
        padding: 24px;
        background:
            radial-gradient(circle at 86% 8%, rgba(34, 211, 238, 0.18), transparent 28%),
            linear-gradient(180deg, #ffffff 0%, #eff6ff 100%);
    }

    .seller-chart-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 18px;
    }

    .seller-chart-head h2 {
        margin: 6px 0 0;
        color: #0f172a;
        font-size: 26px;
        font-weight: 950;
        letter-spacing: -0.05em;
    }

    .seller-chart-head strong {
        color: #0f766e;
        white-space: nowrap;
        font-size: 18px;
    }

    .seller-card-label {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        color: #2563eb;
        font-size: 12px;
        font-weight: 950;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .seller-chart-bars {
        display: grid;
        grid-template-columns: repeat(14, minmax(18px, 1fr));
        gap: 10px;
        align-items: end;
        min-height: 214px;
        padding: 8px 2px 0;
    }

    .seller-chart-day {
        display: grid;
        grid-template-rows: 1fr auto;
        gap: 8px;
        align-items: end;
        min-width: 0;
    }

    .seller-chart-track {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        height: 160px;
        border-radius: 999px;
        background: rgba(148, 163, 184, 0.16);
        overflow: hidden;
    }

    .seller-chart-track span {
        display: block;
        width: 100%;
        border-radius: 999px 999px 0 0;
        background: linear-gradient(180deg, #22d3ee 0%, #2563eb 55%, #0f766e 100%);
        box-shadow: 0 12px 22px rgba(37, 99, 235, 0.28);
    }

    .seller-chart-day small {
        color: #64748b;
        font-size: 11px;
        font-weight: 800;
        text-align: center;
    }

    .seller-dashboard-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
    }

    .seller-stat-card {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        padding: 24px;
        background: #fff;
    }

    .seller-stat-card::after {
        position: absolute;
        right: -34px;
        bottom: -42px;
        width: 130px;
        height: 130px;
        border-radius: 999px;
        content: "";
        opacity: 0.14;
        background: currentColor;
    }

    .seller-stat-card i {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 18px;
        color: white;
        background: currentColor;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
    }

    .seller-stat-card span {
        display: block;
        margin-top: 18px;
        color: #64748b;
        font-size: 13px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .seller-stat-card strong {
        display: block;
        margin-top: 8px;
        color: #0f172a;
        font-size: 30px;
        line-height: 1;
        letter-spacing: -0.05em;
    }

    .seller-stat-card small {
        display: block;
        margin-top: 12px;
        color: #64748b;
        line-height: 1.55;
        font-weight: 700;
    }

    .accent-blue { color: #2563eb; }
    .accent-cyan { color: #0f766e; }
    .accent-amber { color: #f59e0b; }
    .accent-violet { color: #7c3aed; }

    .seller-insight-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(340px, 0.9fr);
        gap: 22px;
    }

    .seller-card-compact {
        padding: 24px;
    }

    .seller-card-head.compact {
        align-items: center;
        margin-bottom: 18px;
    }

    .seller-card-head.compact h2 {
        margin-top: 5px;
        font-size: 24px;
    }

    .seller-link {
        color: #2563eb;
        font-weight: 900;
        text-decoration: none;
    }

    .seller-product-rank {
        display: grid;
        gap: 14px;
    }

    .seller-rank-item {
        display: grid;
        grid-template-columns: 38px minmax(0, 1fr) auto;
        gap: 12px;
        align-items: center;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    }

    .seller-rank-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 14px;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #22d3ee);
        font-weight: 950;
    }

    .seller-rank-item strong,
    .seller-rank-item small {
        display: block;
    }

    .seller-rank-item small {
        margin-top: 3px;
        color: #64748b;
        font-weight: 700;
    }

    .seller-rank-item b {
        color: #0f766e;
        white-space: nowrap;
    }

    .seller-progress {
        height: 7px;
        margin-top: 10px;
        border-radius: 999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .seller-progress span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #22d3ee, #2563eb);
    }

    .seller-status-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .seller-status-tile {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 16px;
        background: #f8fafc;
    }

    .seller-status-tile strong,
    .seller-status-tile span {
        display: block;
    }

    .seller-status-tile strong {
        color: #0f172a;
        font-size: 30px;
        line-height: 1;
    }

    .seller-status-tile span {
        margin-top: 8px;
        color: #64748b;
        font-size: 13px;
        font-weight: 850;
    }

    .seller-status-tile.pending,
    .seller-status-tile.confirmed,
    .seller-status-tile.processing {
        background: #fffbeb;
    }

    .seller-status-tile.shipped {
        background: #eff6ff;
    }

    .seller-status-tile.delivered {
        background: #ecfdf5;
    }

    .seller-status-tile.cancelled {
        background: #fef2f2;
    }

    .seller-table-modern {
        border-spacing: 0 10px;
        border-collapse: separate;
    }

    .seller-table-modern th,
    .seller-table-modern td {
        border-bottom: 0;
        background: #f8fafc;
    }

    .seller-table-modern tbody tr td:first-child {
        border-radius: 18px 0 0 18px;
    }

    .seller-table-modern tbody tr td:last-child {
        border-radius: 0 18px 18px 0;
    }

    .seller-muted {
        color: #64748b;
        font-size: 13px;
        font-weight: 700;
    }

    .seller-money {
        color: #2563eb;
    }

    .seller-btn.mini {
        min-height: 36px;
        padding: 8px 13px;
    }

    .seller-empty.small {
        padding: 24px;
    }

    @media (max-width: 860px) {
        .seller-hero,
        .seller-grid,
        .seller-stats,
        .seller-dashboard-hero,
        .seller-dashboard-grid,
        .seller-insight-grid,
        .seller-hero-mini {
            grid-template-columns: 1fr;
        }
    }
</style>
