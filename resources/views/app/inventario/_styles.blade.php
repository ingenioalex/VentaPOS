@push('head')
<style>
.inv-page {
    --inv-ink: #0f172a;
    --inv-muted: #64748b;
    --inv-border: #e2e8f0;
    --inv-surface: #ffffff;
    --inv-accent: #0f766e;
    --inv-radius: .85rem;
}
.inv-page .inv-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.15rem;
}
.inv-page .inv-header h1 {
    margin: 0;
    font-size: 1.55rem;
    font-weight: 700;
    color: var(--inv-ink);
    letter-spacing: -.02em;
}
.inv-page .inv-subtitle {
    margin: .3rem 0 0;
    font-size: .88rem;
    color: var(--inv-muted);
    line-height: 1.45;
}
.inv-page .inv-subtitle strong { color: var(--inv-ink); }
.inv-page .inv-header-actions {
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    align-items: center;
}
.inv-page .btn-inv-ghost {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .5rem .85rem;
    border-radius: .55rem;
    border: 1px solid var(--inv-border);
    background: var(--inv-surface);
    color: var(--inv-ink);
    text-decoration: none;
    font-size: .84rem;
    font-weight: 600;
}
.inv-page .btn-inv-ghost:hover { background: #f8fafc; color: var(--inv-ink); }
.inv-acciones {
    display: inline-flex;
    gap: .45rem;
    align-items: center;
}
.inv-acciones--block { width: 100%; justify-content: flex-start; }
.inv-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: auto;
    height: auto;
    padding: .42rem .8rem;
    border-radius: .45rem;
    font-size: .78rem;
    font-weight: 700;
    text-decoration: none;
    line-height: 1.2;
    white-space: nowrap;
    border: none;
    box-shadow: 0 1px 3px rgba(15, 23, 42, .08);
    transition: transform .12s ease, box-shadow .12s ease;
}
.inv-acciones--compacto { flex-wrap: wrap; gap: .3rem; }
.inv-acciones--simbolos { gap: .3rem; }
.inv-btn--icon {
    width: 2rem;
    height: 2rem;
    padding: 0;
    font-size: 1rem;
    font-weight: 800;
    border-radius: .4rem;
}
.inv-btn:active { transform: scale(.96); }
.inv-btn--entrada { background: linear-gradient(135deg, #1d4ed8, #2563eb); color: #fff; }
.inv-btn--salida { background: linear-gradient(135deg, #b91c1c, #dc2626); color: #fff; }
.inv-btn--ajuste { background: linear-gradient(135deg, #d97706, #f59e0b); color: #fff; }
.inv-btn--kardex {
    background: linear-gradient(135deg, #0f766e, #14b8a6);
    color: #fff;
}
.inv-leyenda {
    display: flex;
    flex-wrap: wrap;
    gap: .65rem 1.1rem;
    width: 100%;
    padding-bottom: .55rem;
    margin-bottom: .55rem;
    border-bottom: 1px solid var(--inv-border);
    font-size: .78rem;
    color: var(--inv-muted);
}
.inv-leyenda-item {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    font-weight: 600;
}
.inv-leyenda-sym {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.45rem;
    height: 1.45rem;
    border-radius: .3rem;
    font-size: .82rem;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}
.inv-leyenda-sym--entrada { background: #2563eb; }
.inv-leyenda-sym--salida { background: #dc2626; }
.inv-leyenda-sym--ajuste { background: #f59e0b; }
.inv-leyenda-sym--kardex { background: #0f766e; }
.inv-nav-row {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    align-items: center;
    width: 100%;
}
.inv-col-acciones { min-width: 8.5rem; }
.inv-acciones-cell {
    display: flex;
    flex-wrap: wrap;
    gap: .3rem;
    align-items: center;
}
.inv-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: .65rem;
    margin-bottom: 1rem;
}
.inv-kpi {
    background: linear-gradient(180deg, #fafbff 0%, #fff 100%);
    border: 1px solid var(--inv-border);
    border-radius: .75rem;
    padding: .85rem 1rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
}
.inv-kpi-label {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--inv-muted);
}
.inv-kpi-value {
    margin-top: .25rem;
    font-size: 1.25rem;
    font-weight: 800;
    color: var(--inv-ink);
    font-variant-numeric: tabular-nums;
}
.inv-kpi--stock .inv-kpi-value { color: var(--inv-accent); }
.inv-nav {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    align-items: center;
    margin-bottom: 1rem;
    padding: .65rem .75rem;
    background: #f8fafc;
    border: 1px solid var(--inv-border);
    border-radius: .65rem;
}
.inv-nav .btn-tab {
    padding: .4rem .75rem;
    border-radius: .45rem;
    font-size: .82rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid transparent;
}
.inv-nav .btn-tab--active { background: var(--inv-accent); color: #fff; }
.inv-nav .btn-tab--muted { background: #e2e8f0; color: #334155; }
.inv-nav .btn-tab--warn { background: #fef3c7; color: #92400e; border-color: #fde68a; }
.inv-nav .inv-nav-spacer { flex: 1; min-width: .5rem; }
.inv-filtros {
    display: flex;
    flex-wrap: wrap;
    gap: .65rem .85rem;
    align-items: flex-end;
    margin-bottom: 1rem;
    padding: 1rem 1.1rem;
    background: var(--inv-surface);
    border: 1px solid var(--inv-border);
    border-radius: var(--inv-radius);
}
.inv-filtros label {
    display: block;
    font-size: .75rem;
    font-weight: 700;
    color: var(--inv-muted);
    margin-bottom: .25rem;
    text-transform: uppercase;
    letter-spacing: .03em;
}
.inv-filtros input[type="date"],
.inv-filtros input[type="text"],
.inv-filtros select {
    padding: .5rem .65rem;
    border: 1px solid #cbd5e1;
    border-radius: .45rem;
    font-size: .88rem;
    min-width: 9rem;
}
.inv-panel {
    background: var(--inv-surface);
    border: 1px solid var(--inv-border);
    border-radius: var(--inv-radius);
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
}
.inv-panel-head {
    padding: .75rem 1rem;
    border-bottom: 1px solid var(--inv-border);
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--inv-muted);
    background: #f8fafc;
}
.inv-table-wrap { overflow-x: auto; }
.inv-table {
    width: 100%;
    border-collapse: collapse;
    font-size: .86rem;
}
.inv-table th {
    text-align: left;
    padding: .65rem .85rem;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--inv-muted);
    background: #f8fafc;
    border-bottom: 1px solid var(--inv-border);
    white-space: nowrap;
}
.inv-table td {
    padding: .7rem .85rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.inv-table tr:last-child td { border-bottom: none; }
.inv-table .col-num {
    text-align: right;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}
.inv-badge {
    display: inline-block;
    padding: .18rem .5rem;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 700;
    text-transform: capitalize;
}
.inv-badge--entrada { background: #ecfdf5; color: #065f46; }
.inv-badge--salida { background: #fef2f2; color: #991b1b; }
.inv-badge--ajuste { background: #fffbeb; color: #92400e; }
.inv-badge--venta { background: #eff6ff; color: #1e40af; }
.inv-badge--default { background: #f1f5f9; color: #334155; }
.inv-cards { display: none; }
.inv-empty {
    padding: 2rem 1rem;
    text-align: center;
    color: var(--inv-muted);
    font-size: .9rem;
}
.inv-alert-ok {
    background: #ecfdf5;
    border: 1px solid #6ee7b7;
    color: #065f46;
    padding: .75rem 1rem;
    border-radius: .55rem;
    margin-bottom: 1rem;
    font-size: .88rem;
}
@media (max-width: 768px) {
    .inv-page .inv-header h1 { font-size: 1.3rem; }
    .inv-table-wrap { display: none; }
    .inv-cards { display: block; }
    .inv-card-mov {
        padding: .85rem 1rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .inv-card-mov:last-child { border-bottom: none; }
    .inv-card-mov-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: .5rem;
        margin-bottom: .45rem;
    }
    .inv-card-mov-fecha { font-size: .78rem; color: var(--inv-muted); }
    .inv-card-mov-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .35rem .75rem;
        font-size: .82rem;
    }
    .inv-card-mov-grid dt { color: var(--inv-muted); font-size: .72rem; }
    .inv-card-mov-grid dd { margin: 0; font-weight: 600; font-variant-numeric: tabular-nums; }
    .inv-filtros input[type="date"] { width: 100%; min-width: 0; }
    .inv-nav .inv-nav-spacer { display: none; }
    .inv-nav { justify-content: flex-start; }
    .inv-nav .inv-acciones { width: 100%; }
    .inv-nav .inv-acciones .inv-btn { flex: 1; justify-content: center; min-width: 0; }
    .inv-col-acciones { min-width: 0; }
}
</style>
@endpush
