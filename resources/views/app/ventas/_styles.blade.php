@push('head')
<style>
    .ventas-page {
        --v-ink: #0f172a;
        --v-muted: #64748b;
        --v-border: #e2e8f0;
        --v-surface: #ffffff;
        --v-accent: #4338ca;
        --v-accent-soft: #eef2ff;
        --v-success: #047857;
        --v-success-bg: #ecfdf5;
        --v-danger: #b91c1c;
        --v-radius: .85rem;
    }
    .ventas-page .ventas-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
    }
    .ventas-page .ventas-header h1 {
        margin: 0;
        font-size: 1.65rem;
        font-weight: 700;
        color: var(--v-ink);
        letter-spacing: -.02em;
    }
    .ventas-page .ventas-subtitle {
        margin: .25rem 0 0;
        font-size: .88rem;
        color: var(--v-muted);
    }
    .ventas-page .ventas-actions {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .ventas-page .btn-whatsapp {
        background: linear-gradient(135deg, #128c7e, #25d366);
        color: #fff;
        box-shadow: 0 2px 8px rgba(37, 211, 102, .25);
    }
    .ventas-page .btn-ghost {
        background: var(--v-surface);
        color: var(--v-ink);
        border: 1px solid var(--v-border);
    }
    .ventas-page .btn-accent {
        background: linear-gradient(135deg, #4338ca, #6366f1);
        color: #fff;
    }
    .ventas-page .panel {
        background: var(--v-surface);
        border: 1px solid var(--v-border);
        border-radius: var(--v-radius);
        padding: 1.25rem 1.35rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }
    .ventas-page .panel-title {
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--v-muted);
        margin-bottom: 1rem;
    }
    .ventas-page .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: .85rem;
        margin-bottom: 1.15rem;
    }
    .ventas-page .kpi-card {
        background: linear-gradient(180deg, #fafbff 0%, #fff 100%);
        border: 1px solid var(--v-border);
        border-radius: .75rem;
        padding: .95rem 1rem;
        position: relative;
        overflow: hidden;
    }
    .ventas-page .kpi-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #818cf8);
        opacity: .85;
    }
    .ventas-page .kpi-card.kpi-highlight::before {
        background: linear-gradient(90deg, #047857, #34d399);
    }
    .ventas-page .kpi-label {
        display: block;
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--v-muted);
        margin-bottom: .35rem;
    }
    .ventas-page .kpi-value {
        display: block;
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--v-ink);
        line-height: 1.2;
    }
    .ventas-page .kpi-delta {
        display: block;
        margin-top: .3rem;
        font-size: .75rem;
        font-weight: 600;
    }
    .ventas-page .kpi-delta.up { color: var(--v-success); }
    .ventas-page .kpi-delta.down { color: var(--v-danger); }
    .ventas-page .insights-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.25rem;
        font-size: .88rem;
    }
    .ventas-page .insight-block strong {
        display: block;
        color: var(--v-ink);
        margin-bottom: .5rem;
        font-size: .82rem;
    }
    .ventas-page .insight-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .ventas-page .insight-list li {
        display: flex;
        justify-content: space-between;
        gap: .5rem;
        padding: .35rem 0;
        border-bottom: 1px dashed #f1f5f9;
        color: #475569;
    }
    .ventas-page .insight-list li:last-child { border-bottom: none; }
    .ventas-page .filtros-ventas {
        display: flex;
        flex-wrap: wrap;
        gap: .85rem;
        align-items: end;
    }
    .ventas-page .filtros-ventas label {
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--v-muted);
        display: block;
        margin-bottom: .3rem;
    }
    .ventas-page .filtros-ventas input,
    .ventas-page .filtros-ventas select {
        padding: .5rem .65rem;
        border: 1px solid var(--v-border);
        border-radius: .5rem;
        background: #f8fafc;
        font-size: .9rem;
        min-width: 140px;
    }
    .ventas-page .periodo-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: .85rem;
        margin-bottom: 1rem;
    }
    .ventas-page .periodo-stat {
        background: var(--v-accent-soft);
        border: 1px solid #c7d2fe;
        border-radius: .75rem;
        padding: .85rem 1rem;
    }
    .ventas-page .periodo-stat span {
        display: block;
        font-size: .72rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #6366f1;
        margin-bottom: .25rem;
    }
    .ventas-page .periodo-stat strong {
        font-size: 1.25rem;
        color: var(--v-ink);
    }
    .ventas-page .tabla-wrap {
        overflow: auto;
        border-radius: var(--v-radius);
        border: 1px solid var(--v-border);
    }
    .ventas-page table.ventas-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .88rem;
    }
    .ventas-page table.ventas-table th {
        background: #f8fafc;
        color: #475569;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding: .7rem .85rem;
        text-align: left;
        border-bottom: 1px solid var(--v-border);
    }
    .ventas-page table.ventas-table td {
        padding: .7rem .85rem;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }
    .ventas-page table.ventas-table tbody tr:hover {
        background: #fafbff;
    }
    .ventas-page .estado-pill {
        display: inline-block;
        font-size: .72rem;
        font-weight: 700;
        padding: .2rem .55rem;
        border-radius: 999px;
    }
    .ventas-page .estado-pill.emitido {
        background: var(--v-success-bg);
        color: var(--v-success);
    }
    .ventas-page .estado-pill.anulado {
        background: #fef2f2;
        color: var(--v-danger);
    }
    .ventas-page .link-action {
        color: var(--v-accent);
        font-weight: 600;
        text-decoration: none;
    }
    .ventas-page .link-action:hover { text-decoration: underline; }
    /* Detalle */
    .ventas-page .detalle-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 55%, #4338ca 100%);
        color: #fff;
        border-radius: var(--v-radius);
        padding: 1.35rem 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 8px 24px rgba(67, 56, 202, .18);
    }
    .ventas-page .detalle-hero-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
    }
    .ventas-page .detalle-hero h1 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
    }
    .ventas-page .detalle-hero .ventas-actions a {
        color: #e0e7ff;
        font-weight: 600;
        text-decoration: none;
        font-size: .88rem;
    }
    .ventas-page .detalle-hero .ventas-actions a:hover { color: #fff; }
    .ventas-page .meta-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: .85rem;
        margin-top: 1.15rem;
    }
    .ventas-page .meta-item {
        background: rgba(255, 255, 255, .08);
        border: 1px solid rgba(255, 255, 255, .12);
        border-radius: .65rem;
        padding: .65rem .8rem;
    }
    .ventas-page .meta-item .meta-label {
        display: block;
        font-size: .68rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #c7d2fe;
        margin-bottom: .2rem;
    }
    .ventas-page .meta-item .meta-value {
        font-size: .92rem;
        font-weight: 600;
        color: #fff;
    }
    .ventas-page .sunat-banner {
        margin-top: 1rem;
        padding: .75rem 1rem;
        border-radius: .65rem;
        font-size: .85rem;
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .15);
        color: #e0e7ff;
    }
    .ventas-page .sunat-banner .cpe-badge { vertical-align: middle; }
    .ventas-page .anulacion-box {
        margin-top: 1rem;
        padding: .85rem 1rem;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: .65rem;
        font-size: .88rem;
        color: #7f1d1d;
    }
    .ventas-page table.ventas-table tfoot td {
        font-size: .9rem;
        background: #f8fafc;
    }
    .ventas-page table.ventas-table tfoot tr:last-child td {
        font-size: 1rem;
        font-weight: 700;
        color: var(--v-ink);
        border-top: 2px solid var(--v-border);
    }
    .ventas-page .panel-section h3 {
        margin: 0 0 .75rem;
        font-size: .95rem;
        color: var(--v-ink);
    }
    .ventas-page .panel-section p.hint {
        font-size: .85rem;
        color: var(--v-muted);
        margin-bottom: 1rem;
    }
    .ventas-page .form-field {
        margin-bottom: .75rem;
    }
    .ventas-page .form-field label {
        display: block;
        font-weight: 600;
        font-size: .82rem;
        color: #475569;
        margin-bottom: .3rem;
    }
    .ventas-page .form-field input {
        width: 100%;
        padding: .55rem .65rem;
        border: 1px solid var(--v-border);
        border-radius: .5rem;
        background: #f8fafc;
    }
    @media (max-width: 900px) {
        .ventas-page .filtros-ventas { flex-direction: column; align-items: stretch; }
        .ventas-page .filtros-ventas input,
        .ventas-page .filtros-ventas select { width: 100%; }
    }
</style>
@endpush
