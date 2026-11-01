@push('head')
<style>
    .inicio-page {
        --i-ink: #0f172a;
        --i-muted: #64748b;
        --i-border: #e2e8f0;
        --i-accent: #4338ca;
        --i-accent-soft: #eef2ff;
        --i-success: #047857;
        --i-warn: #b45309;
        --i-radius: .85rem;
    }
    .inicio-page .inicio-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
        color: #fff;
        border-radius: var(--i-radius);
        padding: 1.5rem 1.65rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 10px 28px rgba(67, 56, 202, .2);
    }
    .inicio-page .inicio-hero h1 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
        letter-spacing: -.02em;
    }
    .inicio-page .inicio-hero p {
        margin: .35rem 0 0;
        font-size: .9rem;
        color: #c7d2fe;
    }
    .inicio-page .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: .9rem;
        margin-bottom: 1.25rem;
    }
    .inicio-page .kpi-card {
        background: #fff;
        border: 1px solid var(--i-border);
        border-radius: var(--i-radius);
        padding: 1.1rem 1.15rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        transition: box-shadow .15s ease, transform .15s ease;
    }
    .inicio-page .kpi-card:hover {
        box-shadow: 0 6px 18px rgba(15, 23, 42, .08);
        transform: translateY(-1px);
    }
    .inicio-page .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #818cf8);
    }
    .inicio-page .kpi-card.kpi-sales::before {
        background: linear-gradient(90deg, #047857, #34d399);
    }
    .inicio-page .kpi-card.kpi-warn::before {
        background: linear-gradient(90deg, #b45309, #fbbf24);
    }
    .inicio-page .kpi-icon {
        font-size: 1.35rem;
        margin-bottom: .45rem;
        line-height: 1;
    }
    .inicio-page .kpi-label {
        display: block;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: var(--i-muted);
        margin-bottom: .3rem;
    }
    .inicio-page .kpi-value {
        display: block;
        font-size: 1.45rem;
        font-weight: 700;
        color: var(--i-ink);
        line-height: 1.15;
    }
    .inicio-page .kpi-card.kpi-warn .kpi-value { color: var(--i-warn); }
    .inicio-page .alert-panel {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: .75rem;
        background: linear-gradient(90deg, #fffbeb, #fef3c7);
        border: 1px solid #fcd34d;
        border-radius: var(--i-radius);
        padding: .9rem 1.15rem;
        margin-bottom: 1.25rem;
        font-size: .9rem;
        color: #92400e;
    }
    .inicio-page .panel {
        background: #fff;
        border: 1px solid var(--i-border);
        border-radius: var(--i-radius);
        padding: 1.25rem 1.35rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }
    .inicio-page .panel-title {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--i-muted);
        margin-bottom: 1rem;
    }
    .inicio-page .acciones-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
        gap: .85rem;
    }
    .inicio-page .accion-card {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: .35rem;
        padding: 1rem 1.05rem;
        border-radius: .75rem;
        border: 1px solid var(--i-border);
        background: linear-gradient(180deg, #fafbff 0%, #fff 100%);
        text-decoration: none;
        color: var(--i-ink);
        transition: border-color .15s, box-shadow .15s, transform .15s;
        min-height: 108px;
    }
    .inicio-page .accion-card:hover {
        border-color: #c7d2fe;
        box-shadow: 0 8px 20px rgba(67, 56, 202, .12);
        transform: translateY(-2px);
    }
    .inicio-page .accion-card .accion-icon {
        width: 2.1rem;
        height: 2.1rem;
        border-radius: .55rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        margin-bottom: .15rem;
    }
    .inicio-page .accion-card strong {
        font-size: .88rem;
        font-weight: 700;
        line-height: 1.25;
    }
    .inicio-page .accion-card span {
        font-size: .72rem;
        color: var(--i-muted);
        line-height: 1.3;
    }
    .inicio-page .accion-pos .accion-icon { background: #eef2ff; }
    .inicio-page .accion-ventas .accion-icon { background: #f1f5f9; }
    .inicio-page .accion-cierres .accion-icon { background: #fff7ed; }
    .inicio-page .accion-catalogo .accion-icon { background: #ecfdf5; }
    .inicio-page .accion-reportes .accion-icon { background: #eff6ff; }
    .inicio-page .accion-guia .accion-icon { background: #f8fafc; }
    .inicio-page .tabla-wrap {
        overflow: auto;
        border-radius: .65rem;
        border: 1px solid var(--i-border);
    }
    .inicio-page table.inicio-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .88rem;
    }
    .inicio-page table.inicio-table th {
        background: #f8fafc;
        color: #475569;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: .65rem .85rem;
        text-align: left;
        border-bottom: 1px solid var(--i-border);
    }
    .inicio-page table.inicio-table td {
        padding: .65rem .85rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }
    .inicio-page table.inicio-table tbody tr:hover { background: #fafbff; }
    .inicio-page .stock-critico { color: var(--i-warn); font-weight: 700; }
    .inicio-page .link-accent {
        color: var(--i-accent);
        font-weight: 600;
        text-decoration: none;
    }
    .inicio-page .link-accent:hover { text-decoration: underline; }
    .inicio-page .btn-warn {
        background: linear-gradient(135deg, #d97706, #f59e0b);
        color: #fff;
        font-size: .85rem;
        box-shadow: 0 2px 6px rgba(217, 119, 6, .25);
    }
</style>
@endpush
