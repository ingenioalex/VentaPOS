{{-- Estilos paginación sin Tailwind (evita SVG gigantes de la vista por defecto) --}}
.paginacion { margin-top: 1rem; font-size: .88rem; }
.paginacion-info { color: #64748b; margin-bottom: .5rem; font-size: .82rem; }
.paginacion-inner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .35rem;
}
.paginacion-pages {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .25rem;
}
.paginacion-btn,
.paginacion-page {
    display: inline-block;
    padding: .4rem .7rem;
    border: 1px solid #cbd5e1;
    border-radius: .4rem;
    background: #fff;
    color: #334155;
    text-decoration: none;
    font-weight: 600;
    line-height: 1.3;
}
.paginacion-btn:hover,
.paginacion-page:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
.paginacion-page.active {
    background: #0f766e;
    border-color: #0f766e;
    color: #fff;
    cursor: default;
}
.paginacion-btn.disabled,
.paginacion-btn.disabled:hover {
    background: #f1f5f9;
    color: #94a3b8;
    border-color: #e2e8f0;
    cursor: not-allowed;
}
.paginacion-ellipsis {
    padding: .4rem .35rem;
    color: #94a3b8;
}
