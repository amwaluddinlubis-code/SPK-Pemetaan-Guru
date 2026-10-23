@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

{{-- Pagination daisyUI resmi: satu grup join berisi tombol «, nomor halaman
     (aktif = btn-active), dan » — pola dari daisyui.com/components/pagination.
     Di layar kecil grup nomor diganti varian ringkas resmi yang sama
     (« Halaman X / Y ») agar grup join tidak memaksa halaman melebar. --}}
<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation">
            {{-- Grup lengkap: layar sm ke atas --}}
            <div class="hidden sm:block">
                <div class="join">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <button type="button" class="join-item btn btn-sm btn-disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">«</button>
                    @else
                        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" class="join-item btn btn-sm" aria-label="{{ __('pagination.previous') }}">«</button>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <button type="button" class="join-item btn btn-sm btn-disabled" aria-disabled="true">{{ $element }}</button>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <button type="button" wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}" class="join-item btn btn-sm btn-active" aria-current="page">{{ $page }}</button>
                                @else
                                    <button type="button" wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="join-item btn btn-sm" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" class="join-item btn btn-sm" aria-label="{{ __('pagination.next') }}">»</button>
                    @else
                        <button type="button" class="join-item btn btn-sm btn-disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">»</button>
                    @endif
                </div>
            </div>

            {{-- Grup ringkas: layar kecil (« Halaman X / Y ») --}}
            <div class="sm:hidden">
                <div class="join">
                    @if ($paginator->onFirstPage())
                        <button type="button" class="join-item btn btn-sm btn-disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">«</button>
                    @else
                        <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" dusk="previousPageRingkas{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" class="join-item btn btn-sm" aria-label="{{ __('pagination.previous') }}">«</button>
                    @endif

                    <button type="button" class="join-item btn btn-sm" aria-current="page">Halaman {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</button>

                    @if ($paginator->hasMorePages())
                        <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" wire:loading.attr="disabled" dusk="nextPageRingkas{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" class="join-item btn btn-sm" aria-label="{{ __('pagination.next') }}">»</button>
                    @else
                        <button type="button" class="join-item btn btn-sm btn-disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">»</button>
                    @endif
                </div>
            </div>
        </nav>
    @endif
</div>
