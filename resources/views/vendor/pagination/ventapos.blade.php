@if ($paginator->hasPages())
<nav class="paginacion" role="navigation" aria-label="Paginación">
    <p class="paginacion-info">
        @if ($paginator->total() > 0)
            Mostrando {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} de {{ $paginator->total() }}
        @else
            Sin resultados
        @endif
    </p>
    <div class="paginacion-inner">
        @if ($paginator->onFirstPage())
            <span class="paginacion-btn disabled" aria-disabled="true">« Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="paginacion-btn" rel="prev">« Anterior</a>
        @endif

        <div class="paginacion-pages">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="paginacion-ellipsis" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="paginacion-page active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="paginacion-page" aria-label="Ir a página {{ $page }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="paginacion-btn" rel="next">Siguiente »</a>
        @else
            <span class="paginacion-btn disabled" aria-disabled="true">Siguiente »</span>
        @endif
    </div>
</nav>
@endif
