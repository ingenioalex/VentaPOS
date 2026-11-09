<style>
    .dyn-lead {
        margin: .3rem 0 0;
        color: #64748b;
        font-size: .9rem;
        line-height: 1.45;
        max-width: 42rem;
    }
    .stats {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(148px, 1fr));
        gap: .85rem;
        align-items: stretch;
    }
    .stats > .stat,
    .stats > .card {
        --dyn: #0f766e;
        position: relative;
        margin: 0;
        background: linear-gradient(180deg, color-mix(in srgb, var(--dyn) 16%, #fff), #fff 70%);
        border: 1px solid color-mix(in srgb, var(--dyn) 32%, #fff);
        border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
        transition: transform .16s ease, box-shadow .16s ease;
    }
    .stats > .stat {
        border-left: 4px solid var(--dyn);
        padding: .95rem 1rem;
    }
    .stats > .card {
        border-top: 4px solid var(--dyn);
    }
    .stats > :nth-child(6n+1) { --dyn: #4f46e5; }
    .stats > :nth-child(6n+2) { --dyn: #0f766e; }
    .stats > :nth-child(6n+3) { --dyn: #047857; }
    .stats > :nth-child(6n+4) { --dyn: #2563eb; }
    .stats > :nth-child(6n+5) { --dyn: #d97706; }
    .stats > :nth-child(6n+6) { --dyn: #dc2626; }
    .stats > .stat span,
    .stats > .card > span,
    .stats > .card > small {
        display: block;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #64748b;
    }
    .stats > .stat strong,
    .stats > .card strong {
        display: block;
        margin-top: .3rem;
        font-size: 1.35rem;
        line-height: 1.15;
        color: #0f172a;
    }
    main .kpi-grid,
    main .inv-kpi-grid,
    main .periodo-stats,
    main .acciones-grid {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: .65rem;
    }
    main .kpi-grid > .kpi-card,
    main .kpi-grid > .kpi,
    main .inv-kpi-grid > .inv-kpi,
    main .periodo-stats > .periodo-stat,
    main .acciones-grid > .accion-card {
        --dyn: #4f46e5;
        position: relative;
        overflow: hidden;
        min-width: 0;
        background: linear-gradient(180deg, color-mix(in srgb, var(--dyn) 18%, #fff), #fff 68%);
        border: 1px solid color-mix(in srgb, var(--dyn) 28%, #fff);
        border-radius: 1rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    }
    main .kpi-grid > :nth-child(6n+1),
    main .inv-kpi-grid > :nth-child(6n+1),
    main .periodo-stats > :nth-child(6n+1),
    main .acciones-grid > :nth-child(6n+1) { --dyn: #059669; }
    main .kpi-grid > :nth-child(6n+2),
    main .inv-kpi-grid > :nth-child(6n+2),
    main .periodo-stats > :nth-child(6n+2),
    main .acciones-grid > :nth-child(6n+2) { --dyn: #4f46e5; }
    main .kpi-grid > :nth-child(6n+3),
    main .inv-kpi-grid > :nth-child(6n+3),
    main .periodo-stats > :nth-child(6n+3),
    main .acciones-grid > :nth-child(6n+3) { --dyn: #2563eb; }
    main .kpi-grid > :nth-child(6n+4),
    main .inv-kpi-grid > :nth-child(6n+4),
    main .periodo-stats > :nth-child(6n+4),
    main .acciones-grid > :nth-child(6n+4) { --dyn: #0f766e; }
    main .kpi-grid > :nth-child(6n+5),
    main .inv-kpi-grid > :nth-child(6n+5),
    main .periodo-stats > :nth-child(6n+5),
    main .acciones-grid > :nth-child(6n+5) { --dyn: #7c3aed; }
    main .kpi-grid > :nth-child(6n+6),
    main .inv-kpi-grid > :nth-child(6n+6),
    main .periodo-stats > :nth-child(6n+6),
    main .acciones-grid > :nth-child(6n+6) { --dyn: #d97706; }
    main .kpi-grid > .kpi-card.kpi-highlight,
    main .kpi-grid > .kpi-card.kpi-sales { --dyn: #059669; }
    main .kpi-grid > .kpi-card.kpi-warn,
    main .kpi-grid > .kpi.kpi--warn { --dyn: #d97706; }
    main .kpi-grid > .kpi.kpi--danger { --dyn: #dc2626; }
    main .kpi-grid > .kpi-card::before,
    main .kpi-grid > .kpi::before,
    main .inv-kpi-grid > .inv-kpi::before,
    main .periodo-stats > .periodo-stat::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: var(--dyn);
        opacity: 1;
    }
    main .kpi-value,
    main .inv-kpi-value {
        font-size: 1.2rem;
        overflow-wrap: anywhere;
    }
    main .kpi-card,
    main .kpi-grid > .kpi,
    main .inv-kpi,
    main .periodo-stat,
    main .accion-card,
    .glosario-item,
    .resumen-dual__ventas,
    .resumen-dual__arqueo {
        transition: transform .16s ease, box-shadow .16s ease;
    }
    @media (min-width: 960px) {
        main .kpi-grid,
        main .inv-kpi-grid,
        main .periodo-stats {
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        }
        main .acciones-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
        }
    }
    @media (max-width: 700px) {
        main .panel { padding: .85rem; }
        main .kpi-value,
        main .inv-kpi-value { font-size: 1.05rem; }
    }
    @media (hover: hover) and (prefers-reduced-motion: no-preference) {
        .stats > .stat:hover,
        .stats > .card:hover,
        main .kpi-card:hover,
        main .kpi-grid > .kpi:hover,
        main .inv-kpi:hover,
        main .periodo-stat:hover,
        main .accion-card:hover,
        .glosario-item:hover,
        .resumen-dual__ventas:hover,
        .resumen-dual__arqueo:hover {
            transform: scale(1.045);
            box-shadow: 0 12px 28px rgba(15, 23, 42, .12);
            z-index: 2;
        }
    }
    @media (max-width: 800px) {
        main div[style*="grid-template-columns:1fr 1fr"],
        main div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr !important;
        }
    }
    @media (max-width: 640px) {
        .stats { grid-template-columns: 1fr 1fr !important; }
        .stats > .stat strong,
        .stats > .card strong { font-size: 1.12rem; }
        .filtros { display: grid; grid-template-columns: 1fr; }
        .filtros input,
        .filtros select,
        .filtros button { width: 100%; }
        .card[style*="overflow"] { overflow: visible !important; }
        table.data thead {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0 0 0 0);
        }
        table.data,
        table.data tbody,
        table.data tr,
        table.data td { display: block; width: 100%; }
        table.data tr {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: .9rem;
            margin: 0 0 .75rem;
            padding: .25rem .85rem;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .05);
        }
        table.data td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            padding: .5rem 0;
            text-align: right;
        }
        table.data td:last-child { border-bottom: none; }
        table.data td::before {
            content: attr(data-label);
            flex: 0 0 auto;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #64748b;
            text-align: left;
        }
        table.data td[colspan] { display: block; text-align: center; }
        table.data td[colspan]::before { content: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .stats > .stat,
        .stats > .card,
        main .kpi-card,
        .glosario-item,
        .resumen-dual__ventas,
        .resumen-dual__arqueo { transition: none; }
    }
</style>
<script>
document.querySelectorAll('table.data').forEach(function (table) {
    var heads = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) {
        return th.textContent.replace(/\s+/g, ' ').trim();
    });
    if (!heads.length) return;
    table.querySelectorAll('tbody tr').forEach(function (tr) {
        Array.prototype.forEach.call(tr.children, function (td, i) {
            if (td.tagName !== 'TD' || td.hasAttribute('colspan') || td.dataset.label) return;
            if (heads[i]) td.dataset.label = heads[i];
        });
    });
});
</script>
