<div class="entry-body bg-white h-bg-contrast-100 d-flex flex-column justify-content-between p-3 custom-link"
    data-href="{{ $route ?? '#' }}">
    <div>
        <div class="entry-title text-transform-none">
            <h2>
                <a href="{{ $route ?? '#' }}" title="{{ $property->title }} - {{ $property->code }}">
                    {!! LimitCharsFromString($property->title, $limitChars ?? '35') !!}
                </a>
            </h2>
        </div>

        @if ($property->show_address !== 0)
            <span class="text-muted text-smaller fw-bolder ls-1" title="{{ $property->display_web_address }}">
                <i class="fs-5 uil uil-map-marker"></i>
                {{ LimitCharsFromString($property->display_web_address, $limitChars ?? '35') }}
            </span>
        @endif

        <div class="entry-content mt-2">
            <div class="row text-muted text-smaller">
                @if ($property->propertable_type === 'real_estate_individuals')
                    @if (!empty($property->propertable->total_area) && $property->propertable->total_area > 0)
                        <div class="d-none col-auto fw-bolder" title="Área total">
                            <i class="fs-5 uil uil-border-out secondary-color pe-1"></i>
                            <span class="ls-1">
                                {{ $property->propertable->display_total_area }}
                                <span class="text-smaller">m²</span>
                            </span>
                        </div>
                    @endif

                    @if (!empty($property->propertable->useful_area) && $property->propertable->useful_area > 0)
                        <div class="col-auto fw-bolder" title="Área útil">
                            <i class="fs-5 uil uil-ruler-combined secondary-color pe-1"></i>
                            <span class="ls-1">
                                {{ $property->propertable->display_useful_area }}
                                <span class="text-smaller">m²</span>
                            </span>
                        </div>
                    @endif

                    @if (!empty($property->propertable->bedroom) && $property->propertable->bedroom > 0)
                        <div class="col-auto fw-bolder" title="Quarto(s)">
                            <i class="fs-5 uil uil-bed secondary-color pe-1"></i>
                            <span class="ls-1">
                                {{ $property->propertable->bedroom }}
                                <span class="text-smaller d-none">quarto(s)</span>
                            </span>
                        </div>
                    @endif

                    @if (!empty($property->propertable->bathroom) && $property->propertable->bathroom > 0)
                        <div class="col-auto fw-bolder" title="Banheiro(s)">
                            <i class="fs-5 uil uil-bath secondary-color pe-1"></i>
                            <span class="ls-1">
                                {{ $property->propertable->bathroom }}
                                <span class="text-smaller d-none">banheiro(s)</span>
                            </span>
                        </div>
                    @endif

                    @if (!empty($property->propertable->garage) && $property->propertable->garage > 0)
                        <div class="col-auto fw-bolder" title="Vaga(s)">
                            <i class="fs-5 uil uil-car secondary-color pe-1"></i>
                            <span class="ls-1">
                                {{ $property->propertable->garage }}
                                <span class="text-smaller d-none">vaga(s)</span>
                            </span>
                        </div>
                    @endif
                @else
                    @if (!empty($property->propertable->display_total_area) && $property->propertable->min_total_area > 0)
                        <div class="d-none col-auto fw-bolder" title="Área total">
                            <i class="fs-5 uil uil-border-out secondary-color pe-1"></i>
                            <span class="ls-1">
                                {{ $property->propertable->display_total_area }}
                                <span class="text-smaller">m²</span>
                            </span>
                        </div>
                    @endif

                    @if (!empty($property->propertable->display_useful_area) && $property->propertable->min_useful_area > 0)
                        <div class="col-auto fw-bolder" title="Área útil">
                            <i class="fs-5 uil uil-ruler-combined secondary-color pe-1"></i>
                            <span class="ls-1">
                                {{ $property->propertable->display_useful_area }}
                                <span class="text-smaller">m²</span>
                            </span>
                        </div>
                    @endif

                    @if (!empty($property->propertable->display_bedroom) && $property->propertable->min_bedroom > 0)
                        <div class="col-auto fw-bolder" title="Quarto(s)">
                            <i class="fs-5 uil uil-bed secondary-color pe-1"></i>
                            <span class="ls-1">
                                {{ $property->propertable->display_bedroom }}
                                <span class="text-smaller d-none">quarto(s)</span>
                            </span>
                        </div>
                    @endif

                    @if (!empty($property->propertable->display_bathroom) && $property->propertable->min_bathroom > 0)
                        <div class="col-auto fw-bolder" title="Banheiro(s)">
                            <i class="fs-5 uil uil-bath secondary-color pe-1"></i>
                            <span class="ls-1">
                                {{ $property->propertable->display_bathroom }}
                                <span class="text-smaller d-none">banheiro(s)</span>
                            </span>
                        </div>
                    @endif

                    @if (!empty($property->propertable->display_garage) && $property->propertable->min_garage > 0)
                        <div class="col-auto fw-bolder" title="Vaga(s)">
                            <i class="fs-5 uil uil-car secondary-color pe-1"></i>
                            <span class="ls-1">
                                {{ $property->propertable->display_garage }}
                                <span class="text-smaller d-none">vaga(s)</span>
                            </span>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div>
        <div class="row">
            @if ($property->propertable_type === 'real_estate_individuals')
                @if (in_array($property->propertable->role->value, [1, 3]) && $property->propertable->display_sale_price)
                    <div class="col-auto">
                        @if ($property->propertable->display_rent_price)
                            <span class="text-muted text-smaller ls-1">Venda</span>
                        @endif
                        <h4 class="fs-6 fw-bolder mb-0">
                            <span class="text-dark text-smaller">R$</span>
                            {{ $property->propertable->display_sale_price }}
                        </h4>
                    </div>
                @endif

                @if (in_array($property->propertable->role->value, [2, 3]) && $property->propertable->display_rent_price)
                    <div class="col-auto">
                        @if ($property->propertable->display_sale_price)
                            <span class="text-muted text-smaller ls-1">Aluguel</span>
                        @endif
                        <h4 class="fs-6 fw-bolder mb-0">
                            <span class="text-dark text-smaller">R$</span>
                            {{ $property->propertable->display_rent_price }}
                            <span class="text-dark text-smaller">
                                /{{ $property->propertable->display_rent_period }}
                            </span>
                        </h4>
                    </div>
                @endif
            @else
                @if ($property->propertable->display_min_price)
                    <div class="col-auto">
                        @if ($property->propertable->min_price != $property->propertable->max_price)
                            <span class="text-muted text-smaller ls-1">a partir de</span>
                        @endif
                        <h4 class="fs-6 fw-bolder mb-0">
                            <span class="text-dark text-smaller">R$</span>
                            {{ $property->propertable->display_min_price }}
                        </h4>
                    </div>
                @endif
            @endif
        </div>

        @if ($property->display_web_taxes)
            <span class="d-block text-muted text-smaller fw-bolder mt-1">
                {{ $property->display_web_taxes }}
            </span>
        @endif

        <a href="{{ $route ?? '#' }}" class="btn btn-sm btn-link text-start fw-bold secondary-color mt-2 p-0">
            <u>[+] mais detalhes</u>
            <i class="uil-arrow-right"></i>
        </a>
    </div>
</div>
