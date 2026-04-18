@if ($paginator->lastPage() > 1)
<div class="paginacion">
    @if ($paginator->currentPage() > 1)
    <a href="{{ rtrim($baseUrl, '/') . '/?' . http_build_query(['page' => 1, 'caseta' => request()->query('caseta', ''), 'lang' => $currentLang]) }}">
        Primera &laquo;
    </a>
    <a href="{{ rtrim($baseUrl, '/') . '/?' . http_build_query(['page' => $paginator->currentPage() - 1, 'caseta' => request()->query('caseta', ''), 'lang' => $currentLang]) }}">
        &laquo; Anterior
    </a>
    @endif

    @for ($pageNumber = 1; $pageNumber <= $paginator->lastPage(); $pageNumber++)
        <a class="{{ $pageNumber === $paginator->currentPage() ? 'activo' : '' }}"
            href="{{ rtrim($baseUrl, '/') . '/?' . http_build_query(['page' => $pageNumber, 'caseta' => request()->query('caseta', ''), 'lang' => $currentLang]) }}">
            {{ $pageNumber }}
        </a>
        @endfor

        @if ($paginator->currentPage() < $paginator->lastPage())
            <a href="{{ rtrim($baseUrl, '/') . '/?' . http_build_query(['page' => $paginator->currentPage() + 1, 'caseta' => request()->query('caseta', ''), 'lang' => $currentLang]) }}">
                Siguiente &raquo;
            </a>
            <a href="{{ rtrim($baseUrl, '/') . '/?' . http_build_query(['page' => $paginator->lastPage(), 'caseta' => request()->query('caseta', ''), 'lang' => $currentLang]) }}">
                Última &raquo;
            </a>
            @endif
</div>
@endif