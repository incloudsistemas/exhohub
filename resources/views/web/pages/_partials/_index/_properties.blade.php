@unless ($properties['to-sale']->isEmpty())
    <div id="sale-properties-section" class="section my-0 border-bottom">
        <div class="container">
            <div class="heading-block center">
                <h2 class="">
                    {!! $subpages['to-sale']->cmsPost->title !!} <span class="secondary-color">em destaque</span>
                </h2>

                <span>
                    {!! $subpages['to-sale']->cmsPost->excerpt !!}
                </span>
            </div>

            <div class="row col-mb-30">
                @foreach ($properties['to-sale'] as $key => $property)
                    @include('web.properties._partials._index._property-item', [
                        'route' => route('web.real-estate.properties.show', [
                            $property->slug,
                            $property->code,
                        ]),
                    ])
                @endforeach
            </div>

            <div class="center">
                <a href="{{ route('web.real-estate.individuals.index', 'a-venda') }}"
                    class="button button-rounded m-0 ls-1" data-class="down-sm:w-100">
                    <i class="uil uil-arrow-right"></i>
                    <span>Visualizar todos os imóveis</span>
                </a>
            </div>
        </div>
    </div>
@endunless

@unless ($properties['to-rent']->isEmpty())
    <div id="rent-properties-section" class="section bg-transparent my-0 border-bottom">
        <div class="container">
            <div class="heading-block center">
                <h2 class="">
                    {!! $subpages['to-rent']->cmsPost->title !!} <span class="secondary-color">em destaque</span>
                </h2>

                <span>
                    {!! $subpages['to-rent']->cmsPost->excerpt !!}
                </span>
            </div>

            <div class="row col-mb-30">
                @foreach ($properties['to-rent'] as $key => $property)
                    @include('web.properties._partials._index._property-item', [
                        'route' => route('web.real-estate.properties.show', [
                            $property->slug,
                            $property->code,
                        ]),
                    ])
                @endforeach
            </div>

            <div class="center">
                <a href="{{ route('web.real-estate.individuals.index', 'para-alugar') }}"
                    class="button button-rounded m-0 ls-1" data-class="down-sm:w-100">
                    <i class="uil uil-arrow-right"></i>
                    <span>Visualizar todos os imóveis</span>
                </a>
            </div>
        </div>
    </div>
@endunless

@unless ($properties['enterprises']->isEmpty())
    <div id="enterprises-section" class="section my-0 border-bottom">
        <div class="container">
            <div class="heading-block center">
                <h2 class="">
                    {!! $subpages['enterprises']->cmsPost->title !!} <span class="secondary-color">em destaque</span>
                </h2>

                <span>
                    {!! $subpages['enterprises']->cmsPost->excerpt !!}
                </span>
            </div>

            <div class="row col-mb-30">
                @foreach ($properties['enterprises'] as $key => $property)
                    @include('web.properties._partials._index._property-item', [
                        'route' => route('web.real-estate.properties.show', [
                            $property->slug,
                            $property->code,
                        ]),
                    ])
                @endforeach
            </div>

            <div class="center">
                <a href="{{ route('web.real-estate.enterprises.index') }}"
                    class="button button-rounded m-0 ls-1" data-class="down-sm:w-100">
                    <i class="uil uil-arrow-right"></i>
                    <span>Visualizar todos os imóveis</span>
                </a>
            </div>
        </div>
    </div>
@endunless
