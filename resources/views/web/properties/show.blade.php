@extends('web.layouts.default')

{{-- Stylesheets Section --}}
@section('styles')
    @vite('resources/web-assets/css/swiper.css')
@endsection

{{-- Content --}}
@section('content')
    @include('web.properties._partials._show._slider-gallery')

    <!-- Content
    ============================================= -->
    <section id="content">
        <div class="content-wrap">
            <div class="section m-0 header-stick footer-stick">
                <div class="container">
                    <div class="row justify-content-center col-mb-30">
                        <!-- Post Content
                        ============================================= -->
                        <main class="postcontent col-lg-8">
                            <!-- Page Title
                            ============================================= -->
                            @component('web.layouts._partials._page-title', [
                                'classes' => 'mb-0 border-0',
                                'title'   => $page->title,
                            ])
                                @if ($page->propertable_type === 'real_estate_individuals')
                                    @if (in_array($page->propertable->role->value, [1, 3]))
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('web.real-estate.individuals.index', 'a-venda') }}">
                                                <span>Imóveis à venda</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if (in_array($page->propertable->role->value, [2, 3]))
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('web.real-estate.individuals.index', 'para-alugar') }}">
                                                <span>Imóveis para alugar</span>
                                            </a>
                                        </li>
                                    @endif
                                @else
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('web.real-estate.enterprises.index') }}">
                                            <span>Lançamentos</span>
                                        </a>
                                    </li>

                                    <li class="breadcrumb-item">
                                        <a
                                            href="{{ route('web.real-estate.enterprises.role', $page->propertable->display_role_slug) }}">
                                            <span>{{ $page->propertable->display_role }}</span>
                                        </a>
                                    </li>
                                @endif

                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ $page->code }}
                                </li>
                            @endcomponent
                            <!-- .pagetitle end -->

                            <div class="single-post mb-0">
                                <!-- Single Post
                                ============================================= -->
                                <div class="property-item entry">
                                    @include('web.properties._partials._show._entry-meta')

                                    <div class="line my-4"></div>

                                    @include('web.properties._partials._show._entry-content')
                                </div>
                                <!-- .entry end -->

                                @if ($page->comment === 1)
                                    <!-- Comments
                                    ============================================= -->
                                    <div id="comments">
                                        <h3 id="comments-title">
                                            Deixe o seu comentário:
                                        </h3>

                                        <div class="fb-comments" data-width="100%" data-href="{{ Request::url() }}"
                                            data-numposts="5"></div>
                                    </div>
                                    <!-- #comments end -->
                                @endif
                            </div>
                        </main>
                        <!-- .postcontent end -->

                        @include('web.properties._partials._show._sidebar-lead-form-widget')
                    </div>

                    @unless ($relatedProperties->isEmpty())
                        <div class="row col-mb-30 mt-5 mt-lg-0">
                            <div class="col-lg-12">
                                <h4 class="fs-4 fw-medium">
                                    Você pode se interessar:
                                </h4>

                                <!-- Posts
                                ============================================= -->
                                <div id="properties-posts" class="post-grid">
                                    <div class="owl-carousel carousel-widget" data-margin="20"
                                        data-stage-padding="5" data-nav="true" data-pagi="true" data-loop="false"
                                        data-center="false" data-autoplay="5000" data-items-xs="1"
                                        data-items-sm="1" data-items-md="2" data-items-xl="3">
                                        @foreach ($relatedProperties as $key => $property)
                                            <div class="property-item entry">
                                                <div class="grid-inner border shadow-sm rounded-6">
                                                    @include('web.properties._partials._index._entry-image')
                                                    @include('web.properties._partials._index._entry-body')
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <!-- #posts end -->
                            </div>
                        </div>
                    @endunless
                </div>
            </div>
        </div>
    </section>
    <!-- #content end -->
@endsection

{{-- Scripts Section --}}
@section('scripts')
    @vite([
        'resources/js/web/property-page.js',
        'resources/js/web/business-lead-form.js'
    ])

    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v18.0"
        nonce="y5Y7BrV6"></script>
@endsection
