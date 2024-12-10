<!-- Slider
============================================= -->
<section id="slider" class="slider-element swiper_wrapper vh-50 vh-md-90 include-header">
    <div class="slider-inner">
        <div class="swiper swiper-parent">
            <div class="swiper-wrapper">
                <div class="swiper-slide dark">
                    <div class="vertical-middle pb-lg-6">
                        <div class="text-center mb-lg-4">
                            <h1 class="mb-2 text-light">
                                {!! $page->cmsPost->subtitle !!}
                            </h1>

                            <p class="ls-1 text-light">
                                {!! $page->cmsPost->excerpt !!}
                            </p>
                        </div>
                    </div>

                    <div class="video-wrap no-placeholder">
                        <div class="swiper-slide-bg lazy" data-bg="{{ asset('build/web/images/slider-1.jpg') }}"
                            style="background-position: center center; background-size: cover; background-repeat: no-repeat;">
                        </div>

                        <div class="video-overlay" style="background-color: rgba(0, 0, 0, 0.55);"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="slider-search" class="d-none d-lg-block">
    <div class="container">
        <div class="mx-auto">
            <div class="card not-dark rounded-6 shadow-sm">
                <div class="card-body py-4 px-4">
                    @include('web.layouts._partials._form-alert')

                    <form method="get" action="{{ route('web.real-estate.properties.search') }}"
                        id="property-search-form" class="mb-0 row align-items-center not-dark">
                        <div class="col-12 col-lg-10">
                            <div class="row">
                                <div class="col-lg-3 col-12 form-group">
                                    <label for="property-search-role" class="text-smaller ls-1">
                                        O que você procura?
                                    </label>

                                    <select name="role" id="property-search-role"
                                        class="selectpicker form-control">
                                        <option value="a-venda">
                                            Imóveis à venda
                                        </option>

                                        <option value="para-alugar">
                                            Imóveis para alugar
                                        </option>

                                        <option value="lancamentos">
                                            Lançamentos
                                        </option>
                                    </select>
                                </div>

                                <div class="col-lg-4 col-12 form-group">
                                    <label for="property-search-types" class="text-smaller ls-1">
                                        Tipo do imóvel
                                    </label>

                                    <select multiple name="types[]" id="property-search-types"
                                        class="selectpicker form-control" data-actions-box="true"
                                        data-none-selected-text="Todos os imóveis"
                                        data-select-all-text="Selecionar todos"
                                        data-deselect-all-text="Remover todos">
                                        <optgroup label="Residencial">
                                            @foreach ($propertyTypes['residencial'] as $key => $residencialType)
                                                <option value="{{ $key }}_residencial">
                                                    {{ $residencialType }}
                                                </option>
                                            @endforeach
                                        </optgroup>

                                        <optgroup label="Comercial">
                                            @foreach ($propertyTypes['comercial'] as $key => $comercialType)
                                                <option value="{{ $key }}_comercial">
                                                    {{ $comercialType }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="col-lg-5 col-12 form-group">
                                    <label for="property-search-location" class="text-smaller ls-1">
                                        Onde deseja morar?
                                    </label>

                                    <input type="text" name="location"
                                        id="property-search-location" class="form-control"
                                        placeholder="Condomínio, rua, bairro ou cidade"
                                        value="{{ old('location') }}">
                                </div>

                                <div class="col-lg-12 form-group d-none">
                                    <input type="text" name="property-search-botcheck"
                                        id="property-search-botcheck" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-2">
                            <button type="submit" id="property-search-submit"
                                class="button button-rounded ls-1 m-0 w-100">
                                <i class="uil-search"></i>
                                <span>Buscar</span>
                            </button>
                        </div>

                        @if (config('app.g_recapcha_site'))
                            <input type="hidden" class="g-recaptcha-site"
                                value="{{ config('app.g_recapcha_site') }}">
                            <input type="hidden" name="g-recaptcha-response"
                                class="g-recaptcha-response" value="">
                        @endif

                        <input type="hidden" name="prefix" value="property-search-">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- #slider end -->
