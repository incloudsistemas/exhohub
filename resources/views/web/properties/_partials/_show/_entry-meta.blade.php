<!-- Entry Meta
============================================= -->
<div class="entry-meta">
    <div class="row d-flex align-items-center">
        @if ($page->show_address !== 0)
            <div class="col-auto text-muted ls-1"
                title="{{ $page->display_web_address }}">
                <a href="javascript:;" data-scrollto="#g-maps" data-offset="80">
                    <i class="fs-5 uil uil-map-marker"></i>
                    {{ LimitCharsFromString($page->display_web_address, '35') }}
                </a>
            </div>
        @endif

        <div class="col-auto">
            @if ($page->propertable_type === 'real_estate_individuals')
                <span class="badge bg-secondary h-bg-color ls-1 custom-link"
                    data-href="{{ route('web.real-estate.individuals.usage-type', [$page->propertable->display_role_slug, strtolower($page->display_usage), $page->type->slug]) }}">
                    {{ $page->type->name }}
                </span>
            @else
                <span class="badge bg-secondary h-bg-color ls-1 custom-link"
                    data-href="{{ route('web.real-estate.enterprises.usage-type', [strtolower($page->display_usage), $page->type->slug]) }}">
                    {{ $page->type->name }}
                </span>

                <span class="badge bg-color h-bg-secondary ls-1 custom-link"
                    data-href="{{ route('web.real-estate.enterprises.role', $page->propertable->display_role_slug) }}">
                    {{ $page->propertable->display_role }}
                </span>
            @endif
        </div>

        <div class="col-auto">
            <button type="button"
                class="font-size-plus btn btn-outline-secondary border-contrast-200 h-bg-contrast-200 h-text-contrast-900 border-0 ms-1"
                data-scrollto="#cnvs-article-share" data-offset="80">
                <i class="bi-share"></i>
            </button>

            <button type="button"
                class="font-size-plus btn btn-outline-secondary border-contrast-200 h-bg-contrast-200 h-text-contrast-900 border-0 ms-1"
                onclick="window.print();">
                <i class="bi-printer"></i>
            </button>
        </div>
    </div>

    <div class="row mt-3">
        @if ($page->propertable_type === 'real_estate_individuals')
            @if (in_array($page->propertable->role->value, [1, 3]) && $page->propertable->display_sale_price)
                <div class="col-auto">
                    @if ($page->propertable->display_rent_price)
                        <span class="text-muted text-smaller ls-1">Venda</span>
                    @endif
                    <h4 class="fs-5 fw-bolder mb-0">
                        <span class="text-dark text-smaller">R$</span>
                        {{ $page->propertable->display_sale_price }}
                    </h4>
                </div>
            @endif

            @if (in_array($page->propertable->role->value, [2, 3]) && $page->propertable->display_rent_price)
                <div class="col-auto">
                    @if ($page->propertable->display_sale_price)
                        <span class="text-muted text-smaller ls-1">Aluguel</span>
                    @endif
                    <h4 class="fs-5 fw-bolder mb-0">
                        <span class="text-dark text-smaller">R$</span>
                        {{ $page->propertable->display_rent_price }}
                        <span class="text-dark text-smaller">
                            /{{ $page->propertable->display_rent_period }}
                        </span>
                    </h4>
                </div>
            @endif
        @else
            @if ($page->propertable->display_min_price)
                <div class="col-auto">
                    @if ($page->propertable->min_price != $page->propertable->max_price)
                        <span class="text-muted text-smaller ls-1">a partir de</span>
                    @endif
                    <h4 class="fs-5 fw-bolder mb-0">
                        <span class="text-dark text-smaller">R$</span>
                        {{ $page->propertable->display_min_price }}
                    </h4>
                </div>
            @endif
        @endif
    </div>

    @if ($page->display_web_taxes)
        <span class="d-block text-muted text-smaller mt-1">
            {{ $page->display_web_taxes }}
        </span>
    @endif

    <div class="row mt-3 text-muted">
        @if ($page->propertable_type === 'real_estate_individuals')
            @if (!empty($page->propertable->total_area) && $page->propertable->total_area > 0)
                <div class="col-auto fw-bolder" title="Área total">
                    <i class="fs-4 uil uil-border-out secondary-color pe-1"></i>
                    <span class="ls-1">
                        {{ $page->propertable->display_total_area }}
                        <span class="text-smaller">m²</span>
                    </span>
                </div>
            @endif

            @if (!empty($page->propertable->useful_area) && $page->propertable->useful_area > 0)
                <div class="col-auto fw-bolder" title="Área útil">
                    <i class="fs-4 uil uil-ruler-combined secondary-color pe-1"></i>
                    <span class="ls-1">
                        {{ $page->propertable->display_useful_area }}
                        <span class="text-smaller">m²</span>
                    </span>
                </div>
            @endif

            @if (!empty($page->propertable->bedroom) && $page->propertable->bedroom > 0)
                <div class="col-auto fw-bolder" title="Quarto(s)">
                    <i class="fs-4 uil uil-bed secondary-color pe-1"></i>
                    <span class="ls-1">
                        {{ $page->propertable->bedroom }}
                        <span class="text-smaller d-none">quarto(s)</span>
                    </span>
                </div>
            @endif

            @if (!empty($page->propertable->bathroom) && $page->propertable->bathroom > 0)
                <div class="col-auto fw-bolder" title="Banheiro(s)">
                    <i class="fs-4 uil uil-bath secondary-color pe-1"></i>
                    <span class="ls-1">
                        {{ $page->propertable->bathroom }}
                        <span class="text-smaller d-none">banheiro(s)</span>
                    </span>
                </div>
            @endif

            @if (!empty($page->propertable->garage) && $page->propertable->garage > 0)
                <div class="col-auto fw-bolder" title="Vaga(s)">
                    <i class="fs-4 uil uil-car secondary-color pe-1"></i>
                    <span class="ls-1">
                        {{ $page->propertable->garage }}
                        <span class="text-smaller d-none">vaga(s)</span>
                    </span>
                </div>
            @endif
        @else
            @if (!empty($page->propertable->display_total_area) && $page->propertable->min_total_area > 0)
                <div class="col-auto fw-bolder" title="Área total">
                    <i class="fs-5 uil uil-border-out secondary-color pe-1"></i>
                    <span class="ls-1">
                        {{ $page->propertable->display_total_area }}
                        <span class="text-smaller">m²</span>
                    </span>
                </div>
            @endif

            @if (!empty($page->propertable->display_useful_area) && $page->propertable->min_useful_area > 0)
                <div class="col-auto fw-bolder" title="Área útil">
                    <i class="fs-4 uil uil-ruler-combined secondary-color pe-1"></i>
                    <span class="ls-1">
                        {{ $page->propertable->display_useful_area }}
                        <span class="text-smaller">m²</span>
                    </span>
                </div>
            @endif

            @if (!empty($page->propertable->display_bedroom) && $page->propertable->min_bedroom > 0)
                <div class="col-auto fw-bolder" title="Quarto(s)">
                    <i class="fs-4 uil uil-bed secondary-color pe-1"></i>
                    <span class="ls-1">
                        {{ $page->propertable->display_bedroom }}
                        <span class="text-smaller d-none">quarto(s)</span>
                    </span>
                </div>
            @endif

            @if (!empty($page->propertable->display_bathroom) && $page->propertable->min_bathroom > 0)
                <div class="col-auto fw-bolder" title="Banheiro(s)">
                    <i class="fs-4 uil uil-bath secondary-color pe-1"></i>
                    <span class="ls-1">
                        {{ $page->propertable->display_bathroom }}
                        <span class="text-smaller d-none">banheiro(s)</span>
                    </span>
                </div>
            @endif

            @if (!empty($page->propertable->display_garage) && $page->propertable->min_garage > 0)
                <div class="col-auto fw-bolder" title="Vaga(s)">
                    <i class="fs-4 uil uil-car secondary-color pe-1"></i>
                    <span class="ls-1">
                        {{ $page->propertable->display_garage }}
                        <span class="text-smaller d-none">vaga(s)</span>
                    </span>
                </div>
            @endif
        @endif
    </div>
</div>
<!-- .entry-meta end -->
