@push('head')
<style>
    .rep-inv {
        --ri-ink: #0f172a;
        --ri-muted: #64748b;
        --ri-line: #e2e8f0;
        --ri-radius: 1rem;
    }
    .rep-inv .rep-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: .85rem;
    }
    .rep-inv h1 {
        margin: 0;
        font-size: 1.55rem;
        letter-spacing: -.02em;
    }
    .rep-inv .rep-lead {
        margin: .25rem 0 0;
        color: var(--ri-muted);
        font-size: .9rem;
        max-width: 36rem;
    }
    .rep-inv .rep-exports {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
    }
    .rep-inv .rep-chip {
        display: inline-flex;
        align-items: center;
        padding: .45rem .75rem;
        border-radius: .6rem;
        color: #fff;
        font-size: .8rem;
        font-weight: 700;
        text-decoration: none;
        transition: transform .16s ease, box-shadow .16s ease;
    }
    .rep-inv .rep-chip:hover {
        color: #fff;
        transform: scale(1.06);
        box-shadow: 0 8px 18px rgba(15, 23, 42, .16);
    }
    .rep-inv .rep-chip.slate { background: #475569; }
    .rep-inv .rep-chip.teal { background: #0f766e; }
    .rep-inv .rep-chip.blue { background: #1d4ed8; }
    .rep-inv .rep-section {
        margin: 1.1rem 0 .55rem;
    }
    .rep-inv .rep-section h2 {
        margin: 0;
        font-size: 1rem;
    }
    .rep-inv .rep-section p {
        margin: .15rem 0 0;
        color: var(--ri-muted);
        font-size: .82rem;
    }
    .rep-inv .rep-kpis {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(148px, 1fr));
        gap: .75rem;
        margin-bottom: .35rem;
    }
    .rep-inv .rep-kpi {
        background: #fff;
        border: 1px solid var(--ri-line);
        border-radius: var(--ri-radius);
        padding: .9rem .95rem .85rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        transition: transform .16s ease, box-shadow .16s ease;
    }
    .rep-inv .rep-kpi::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        background: var(--ri-tone, #4338ca);
    }
    .rep-inv .rep-kpi:hover {
        transform: scale(1.045);
        z-index: 1;
        box-shadow: 0 12px 24px rgba(15, 23, 42, .12);
    }
    .rep-inv .rep-kpi span {
        display: block;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--ri-muted);
    }
    .rep-inv .rep-kpi strong {
        display: block;
        margin-top: .28rem;
        font-size: 1.35rem;
        line-height: 1.15;
        color: var(--ri-ink);
    }
    .rep-inv .rep-kpi small {
        display: block;
        margin-top: .25rem;
        color: var(--ri-muted);
        font-size: .75rem;
    }
    .rep-inv .tone-indigo { --ri-tone: #4338ca; }
    .rep-inv .tone-sky { --ri-tone: #0284c7; }
    .rep-inv .tone-teal { --ri-tone: #0f766e; }
    .rep-inv .tone-green { --ri-tone: #047857; }
    .rep-inv .tone-amber { --ri-tone: #d97706; }
    .rep-inv .tone-red { --ri-tone: #dc2626; }
    .rep-inv .tone-orange { --ri-tone: #ea580c; }
    .rep-inv .tone-blue { --ri-tone: #1d4ed8; }
    .rep-inv .tone-violet { --ri-tone: #7c3aed; }
    .rep-inv .rep-kpi.alerta strong { color: var(--ri-tone); }
    .rep-inv .rep-filtros {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr)) auto;
        gap: .65rem;
        align-items: end;
        background: #fff;
        border: 1px solid var(--ri-line);
        border-radius: var(--ri-radius);
        padding: .9rem;
        margin: .85rem 0 1rem;
    }
    .rep-inv .rep-filtros label {
        display: block;
        font-size: .75rem;
        font-weight: 700;
        color: var(--ri-muted);
        margin-bottom: .25rem;
    }
    .rep-inv .rep-filtros input,
    .rep-inv .rep-filtros select {
        width: 100%;
        padding: .5rem .55rem;
        border: 1px solid #cbd5e1;
        border-radius: .5rem;
        background: #fff;
    }
    .rep-inv .rep-layout {
        display: grid;
        grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
        gap: 1rem;
        align-items: start;
    }
    .rep-inv .rep-panel {
        background: #fff;
        border: 1px solid var(--ri-line);
        border-radius: var(--ri-radius);
        padding: 1rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        transition: transform .16s ease, box-shadow .16s ease;
    }
    .rep-inv .rep-panel:hover {
        transform: scale(1.012);
        box-shadow: 0 10px 22px rgba(15, 23, 42, .08);
    }
    .rep-inv .rep-panel h3 {
        margin: 0 0 .15rem;
        font-size: 1rem;
    }
    .rep-inv .rep-panel .hint {
        margin: 0 0 .85rem;
        color: var(--ri-muted);
        font-size: .78rem;
    }
    .rep-inv .rep-tipos {
        display: flex;
        flex-direction: column;
        gap: .5rem;
    }
    .rep-inv .rep-tipo {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: .15rem .75rem;
        padding: .65rem .7rem;
        border-radius: .7rem;
        background: #f8fafc;
        border: 1px solid var(--ri-line);
        transition: transform .16s ease;
    }
    .rep-inv .rep-tipo:hover { transform: scale(1.03); }
    .rep-inv .rep-tipo b { font-size: .92rem; }
    .rep-inv .rep-tipo span { color: var(--ri-muted); font-size: .75rem; }
    .rep-inv .rep-tipo strong { font-size: 1.05rem; grid-row: 1 / span 2; align-self: center; }
    .rep-inv .rep-total {
        margin: .8rem 0 0;
        font-size: .82rem;
        color: var(--ri-muted);
    }
    .rep-inv .rep-table-wrap { overflow-x: auto; }
    .rep-inv table.rep-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .9rem;
    }
    .rep-inv table.rep-table th,
    .rep-inv table.rep-table td {
        padding: .6rem .45rem;
        text-align: left;
        border-bottom: 1px solid var(--ri-line);
        vertical-align: middle;
    }
    .rep-inv table.rep-table th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--ri-muted);
    }
    .rep-inv table.rep-table tbody tr { transition: background .12s ease; }
    .rep-inv table.rep-table tbody tr:hover { background: #f8fafc; }
    .rep-inv .sku { font-family: ui-monospace, monospace; font-size: .8rem; color: #334155; }
    .rep-inv .prod-link { font-weight: 600; color: #1d4ed8; text-decoration: none; }
    .rep-inv .prod-link:hover { text-decoration: underline; }
    .rep-inv .badge {
        display: inline-block;
        margin-left: .35rem;
        padding: .08rem .4rem;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }
    .rep-inv .badge.bajo { background: #ffedd5; color: #c2410c; }
    .rep-inv .badge.cero { background: #fee2e2; color: #b91c1c; }
    .rep-inv .num { font-variant-numeric: tabular-nums; white-space: nowrap; }
    .rep-inv .vacio { color: var(--ri-muted); padding: .8rem 0; }
    @media (max-width: 900px) {
        .rep-inv .rep-filtros { grid-template-columns: 1fr 1fr; }
        .rep-inv .rep-filtros button { grid-column: 1 / -1; }
        .rep-inv .rep-layout { grid-template-columns: 1fr; }
        .rep-inv .rep-panel:hover,
        .rep-inv .rep-kpi:hover,
        .rep-inv .rep-tipo:hover,
        .rep-inv .rep-chip:hover { transform: none; }
    }
    @media (max-width: 640px) {
        .rep-inv .rep-kpis { grid-template-columns: 1fr 1fr; }
        .rep-inv .rep-kpi strong { font-size: 1.12rem; }
        .rep-inv .rep-exports { width: 100%; }
        .rep-inv .rep-chip { flex: 1 1 calc(50% - .45rem); justify-content: center; }
        .rep-inv .rep-filtros { grid-template-columns: 1fr; }
        .rep-inv table.rep-table thead { display: none; }
        .rep-inv table.rep-table,
        .rep-inv table.rep-table tbody,
        .rep-inv table.rep-table tr,
        .rep-inv table.rep-table td { display: block; width: 100%; }
        .rep-inv table.rep-table tr {
            border: 1px solid var(--ri-line);
            border-radius: .75rem;
            margin-bottom: .65rem;
            padding: .35rem .55rem;
            background: #fff;
        }
        .rep-inv table.rep-table td {
            display: flex;
            justify-content: space-between;
            gap: .75rem;
            border: 0;
            padding: .35rem 0;
        }
        .rep-inv table.rep-table td::before {
            content: attr(data-label);
            color: var(--ri-muted);
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .rep-inv .rep-kpi,
        .rep-inv .rep-panel,
        .rep-inv .rep-tipo,
        .rep-inv .rep-chip { transition: none; }
        .rep-inv .rep-kpi:hover,
        .rep-inv .rep-panel:hover,
        .rep-inv .rep-tipo:hover,
        .rep-inv .rep-chip:hover { transform: none; }
    }
</style>
@endpush
