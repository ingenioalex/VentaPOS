@push('head')
<style>
    .productos-page {
        --p-ink: #0f172a;
        --p-muted: #64748b;
        --p-border: #e2e8f0;
        --p-surface: #ffffff;
        --p-accent: #0f766e;
        --p-accent-2: #14b8a6;
        --p-accent-soft: #f0fdfa;
        --p-accent-border: #99f6e4;
        --p-warn: #b45309;
        --p-warn-bg: #fffbeb;
        --p-danger: #b91c1c;
        --p-danger-bg: #fef2f2;
        --p-radius: .85rem;
    }
    .productos-page .prod-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .productos-page .prod-header h1 {
        margin: 0;
        font-size: 1.65rem;
        font-weight: 700;
        color: var(--p-ink);
        letter-spacing: -.02em;
    }
    .productos-page .prod-subtitle {
        margin: .25rem 0 0;
        font-size: .88rem;
        color: var(--p-muted);
    }
    .productos-page .prod-actions {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .productos-page .btn-cat {
        background: #fff;
        color: #475569;
        border: 1px solid var(--p-border);
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }
    .productos-page .btn-import {
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        color: #fff;
        box-shadow: 0 2px 8px rgba(37, 99, 235, .22);
    }
    .productos-page .btn-new {
        background: linear-gradient(135deg, #0f766e, #14b8a6);
        color: #fff;
        box-shadow: 0 2px 8px rgba(15, 118, 110, .28);
    }
    .productos-page .btn-search {
        background: linear-gradient(135deg, #0f766e, #0d9488);
        color: #fff;
        min-width: 110px;
    }
    .productos-page .panel {
        background: var(--p-surface);
        border: 1px solid var(--p-border);
        border-radius: var(--p-radius);
        padding: 1.25rem 1.35rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }
    .productos-page .panel-title {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--p-muted);
        margin-bottom: 1rem;
    }
    .productos-page .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: .85rem;
        margin-bottom: 1rem;
    }
    .productos-page .kpi-card {
        background: linear-gradient(180deg, #f0fdfa 0%, #fff 100%);
        border: 1px solid var(--p-accent-border);
        border-radius: .75rem;
        padding: .95rem 1rem;
        position: relative;
        overflow: hidden;
    }
    .productos-page .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #0f766e, #2dd4bf);
        opacity: .9;
    }
    .productos-page .kpi-card.kpi-warn::before {
        background: linear-gradient(90deg, #d97706, #fbbf24);
    }
    .productos-page .kpi-card.kpi-warn {
        background: linear-gradient(180deg, #fffbeb 0%, #fff 100%);
        border-color: #fde68a;
    }
    .productos-page .kpi-label {
        display: block;
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--p-muted);
        margin-bottom: .35rem;
    }
    .productos-page .kpi-value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--p-ink);
        line-height: 1.2;
    }
    .productos-page .alert-stock {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: .75rem;
        padding: .85rem 1.1rem;
        margin-bottom: 1rem;
        border-radius: var(--p-radius);
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        border: 1px solid #fcd34d;
        color: #92400e;
        font-size: .9rem;
    }
    .productos-page .alert-stock strong { color: #78350f; }
    .productos-page .filtros-productos {
        display: flex;
        flex-wrap: wrap;
        gap: .85rem;
        align-items: end;
    }
    .productos-page .filtros-productos .campo-buscar { flex: 1; min-width: 200px; }
    .productos-page .filtros-productos label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--p-muted);
        display: block;
        margin-bottom: .3rem;
    }
    .productos-page .filtros-productos input,
    .productos-page .filtros-productos select {
        padding: .5rem .65rem;
        border: 1px solid var(--p-border);
        border-radius: .5rem;
        background: #f8fafc;
        font-size: .88rem;
        min-width: 130px;
        max-width: 100%;
    }
    .productos-page .filtros-productos .campo-buscar input { width: 100%; max-width: 380px; }
    .productos-page .tabla-wrap {
        overflow: auto;
        border-radius: var(--p-radius);
        border: 1px solid var(--p-border);
    }
    .productos-page table.productos-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .88rem;
        table-layout: auto;
    }
    .productos-page table.productos-table th {
        background: #f8fafc;
        color: #475569;
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: .65rem .75rem;
        text-align: left;
        border-bottom: 1px solid var(--p-border);
        white-space: nowrap;
        vertical-align: bottom;
    }
    .productos-page table.productos-table th.col-num,
    .productos-page table.productos-table td.col-num {
        text-align: right;
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }
    .productos-page table.productos-table th.col-center,
    .productos-page table.productos-table td.col-center {
        text-align: center;
    }
    .productos-page table.productos-table th.col-acciones,
    .productos-page table.productos-table td.col-acciones {
        text-align: right;
        white-space: nowrap;
    }
    .productos-page table.productos-table th.col-nombre {
        min-width: 160px;
    }
    .productos-page table.productos-table td {
        padding: .6rem .75rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
        text-align: left;
    }
    .productos-page table.productos-table tbody tr:hover {
        background: #f8fafc;
    }
    .productos-page table.productos-table tbody tr:nth-child(even) {
        background: #fafafa;
    }
    .productos-page table.productos-table tbody tr:nth-child(even):hover {
        background: #f8fafc;
    }
    .productos-page .mono {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: .82rem;
        color: #475569;
    }
    .productos-page .prod-nombre {
        font-weight: 600;
        color: var(--p-ink);
    }
    .productos-page .txt-cat {
        color: #047857;
        font-weight: 500;
    }
    .productos-page .txt-sub {
        color: #0f766e;
        font-weight: 600;
    }
    .productos-page .txt-um {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: .82rem;
        color: #475569;
        font-weight: 600;
    }
    .productos-page .precio-venta {
        font-weight: 600;
        color: #0f766e;
    }
    .productos-page .precio-costo {
        color: var(--p-muted);
        font-size: .85rem;
    }
    .productos-page .stock-ok { color: #334155; }
    .productos-page .stock-bajo { color: var(--p-warn); font-weight: 600; }
    .productos-page .stock-neg { color: var(--p-danger); font-weight: 600; }
    .productos-page .txt-estado.activo {
        color: #047857;
        font-weight: 600;
    }
    .productos-page .txt-estado.inactivo {
        color: #64748b;
    }
    .productos-page .link-action {
        color: var(--p-accent);
        font-weight: 600;
        text-decoration: none;
        font-size: .85rem;
    }
    .productos-page .link-action:hover { text-decoration: underline; color: #0d9488; }
    .productos-page .link-action.link-kardex { color: #2563eb; }
    .productos-page .empty-row td {
        padding: 2rem 1rem !important;
        text-align: center;
        color: var(--p-muted);
        font-size: .92rem;
    }
    .productos-page .pagination-wrap {
        margin-top: 1rem;
        font-size: .88rem;
    }
    /* Formulario */
    .productos-page .form-layout {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        align-items: start;
    }
    .productos-page .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .productos-page .form-group { margin-bottom: .85rem; }
    .productos-page .form-group label {
        font-weight: 600;
        font-size: .82rem;
        color: #475569;
        display: block;
        margin-bottom: .3rem;
    }
    .productos-page .form-group input,
    .productos-page .form-group select {
        width: 100%;
        padding: .55rem .65rem;
        border: 1px solid var(--p-border);
        border-radius: .5rem;
        background: #f8fafc;
        font-size: .9rem;
    }
    .productos-page .form-group input:focus,
    .productos-page .form-group select:focus,
    .productos-page .filtros-productos input:focus,
    .productos-page .filtros-productos select:focus {
        outline: none;
        border-color: var(--p-accent-2);
        box-shadow: 0 0 0 3px rgba(20, 184, 166, .15);
        background: #fff;
    }
    .productos-page .seccion-titulo {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--p-accent);
        margin: 1.15rem 0 .65rem;
        padding-bottom: .4rem;
        border-bottom: 2px solid var(--p-accent-border);
    }
    .productos-page .seccion-titulo:first-child { margin-top: 0; }
    .productos-page .calc-box {
        background: linear-gradient(135deg, #f0fdfa, #ecfdf5);
        border: 1px solid var(--p-accent-border);
        padding: .85rem 1rem;
        border-radius: .65rem;
        font-size: .9rem;
        color: #134e4a;
    }
    .productos-page .calc-box div { margin-bottom: .25rem; }
    .productos-page .calc-box div:last-child { margin-bottom: 0; font-weight: 700; }
    .productos-page .form-hero {
        background: linear-gradient(135deg, #134e4a 0%, #0f766e 50%, #14b8a6 100%);
        color: #fff;
        border-radius: var(--p-radius);
        padding: 1.25rem 1.4rem;
        margin-bottom: 1rem;
        box-shadow: 0 8px 24px rgba(15, 118, 110, .2);
    }
    .productos-page .form-hero h1 {
        margin: 0;
        font-size: 1.45rem;
        font-weight: 700;
        color: #fff;
    }
    .productos-page .form-hero p {
        margin: .35rem 0 0;
        font-size: .88rem;
        color: #ccfbf1;
    }
    .productos-page .form-hero-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .productos-page .btn-kardex {
        background: rgba(255,255,255,.15);
        color: #fff;
        border: 1px solid rgba(255,255,255,.25);
    }
    .productos-page .hint {
        font-size: .8rem;
        color: var(--p-muted);
        margin-top: .25rem;
    }
    .productos-page .import-hero {
        background: linear-gradient(135deg, #1e3a5f, #2563eb);
        color: #fff;
        border-radius: var(--p-radius);
        padding: 1.25rem 1.4rem;
        margin-bottom: 1rem;
    }
    .productos-page .import-hero h1 {
        margin: 0;
        font-size: 1.45rem;
        color: #fff;
    }
    .productos-page .import-hero p {
        margin: .35rem 0 0;
        font-size: .88rem;
        color: #bfdbfe;
    }
    .productos-page .btn-back {
        background: rgba(255,255,255,.12);
        color: #fff;
        border: 1px solid rgba(255,255,255,.2);
    }
    .productos-page .import-drop {
        border: 2px dashed #cbd5e1;
        border-radius: .65rem;
        padding: 1rem;
        background: #f8fafc;
        transition: border-color .15s, background .15s;
    }
    .productos-page .import-drop:hover {
        border-color: var(--p-accent-2);
        background: #f0fdfa;
    }
    @media (max-width: 900px) {
        .productos-page .filtros-productos { flex-direction: column; align-items: stretch; }
        .productos-page .filtros-productos input,
        .productos-page .filtros-productos select { width: 100%; min-width: 0; }
        .productos-page .form-layout,
        .productos-page .form-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush
