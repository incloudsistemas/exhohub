<!DOCTYPE html>
<html dir="ltr" lang="pt-BR">

<head>
    <base href="{{ config('app.url') }}">

    @if (isset($noIndex))
        <meta name="robots" content="noindex, nofollow" />
    @endif

    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge">
    <meta name="copyright"
        content="© {{ date('Y') > '2024' ? '2024 - ' . date('Y') : '2024' }} {{ config('app.name') }} {{ config('app.url') }}">
    <meta name="author" content="InCloud - Marketing Digital e Desenvolvimento Web. https://incloudsistemas.com.br" />

    <!-- CSRF Token -->
    <meta content="{{ csrf_token() }}" name="csrf-token" />

    <!-- Font Imports -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=PT+Serif:ital@0;1&display=swap"
        rel="stylesheet">

    <!-- Core Style -->
    @vite([
        'resources/web-assets/style.css',
        'resources/web-assets/css/font-icons.css',
        'resources/web-assets/css/components/bs-select.css'
    ])

    {{-- Styles injected in pages --}}
    @yield('styles')

    <!-- Custom CSS -->
    @vite('resources/css/web/custom.css')

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Favicons
    ================================================== -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/png" sizes="16x16">
    <link rel="apple-touch-icon" href="{{ asset('build/web/images/apple-touch-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('build/web/images/apple-touch-icon-72x72.png') }}" sizes="72x72">
    <link rel="apple-touch-icon" href="{{ asset('build/web/images/apple-touch-icon-114x114.png') }}" sizes="114x114">
    <link rel="apple-touch-icon" href="{{ asset('build/web/images/apple-touch-icon-144x144.png') }}" sizes="144x144">

    <!-- Document SEO MetaTags
    ============================================= -->
    {!! SEO::generate() !!}
</head>

<body class="stretched page-transition side-panel-left" data-loader="2" data-loader-color="var(--cnvs-themecolor)"
    data-animation-in="fadeIn" data-speed-in="1500" data-animation-out="fadeOut" data-speed-out="800">
    @if ($agent->isMobile())
        <div class="body-overlay"></div>

        <!-- Side Panel
        ============================================= -->
        <div id="side-panel" class="bg-white">
            <div id="side-panel-trigger-close" class="side-panel-trigger">
                <a href="#"><i class="bi-x-lg"></i></a>
            </div>

            <div class="side-panel-wrap">
                <div class="widget w-100">
                    @include('web.properties._partials._property-search-form')
                </div>
            </div>
        </div>
        <!-- #side-panel end -->
    @endif

    <!-- Document Wrapper
    ============================================= -->
    <div id="wrapper">
        <!-- Header
        ============================================= -->
        <header id="header"
            class="header-size-md {{ isset($page->cmsPost) && $page->cmsPost->slug === 'index' ? 'transparent-header transparent-header-responsive' : '' }}"
            data-sticky-shrink="true" data-sticky-class="not-dark" data-sticky-offset="0" data-sticky-shrink-offset="0"
            data-mobile-sticky="true">
            <div id="header-wrap">
                <div class="container">
                    <div class="header-row">
                        <!-- Logo
                        ============================================= -->
                        <div id="logo">
                            <a href="{{ route('web.pgs.index') }}">
                                <img class="logo-default"
                                    srcset="{{ asset('build/web/images/exho-logo.png') }}, {{ asset('build/web/images/exho-logo@2x.png') }} 2x"
                                    src="{{ asset('build/web/images/exho-logo@2x.png') }}"
                                    alt="{{ config('app.name') }}">

                                <img class="logo-dark"
                                    srcset="{{ asset('build/web/images/exho-logo-dark.png') }}, {{ asset('build/web/images/exho-logo-dark@2x.png') }} 2x"
                                    src="{{ asset('build/web/images/exho-logo-dark@2x.png') }}"
                                    alt="{{ config('app.name') }}">

                                <img class="logo-mobile"
                                    srcset="{{ asset('build/web/images/exho-logo-sticky.png') }}, {{ asset('build/web/images/exho-logo-sticky@2x.png') }} 2x"
                                    src="{{ asset('web-build/images/exho-logo-sticky@2x.png') }}"
                                    alt="{{ config('app.name') }}">

                                @if (!$agent->isMobile())
                                    <img class="logo-sticky"
                                        srcset="{{ asset('build/web/images/exho-logo-sticky.png') }}, {{ asset('build/web/images/exho-logo-sticky@2x.png') }} 2x"
                                        src="{{ asset('build/web/images/exho-logo-sticky@2x.png') }}"
                                        alt="{{ config('app.name') }}">
                                @endif
                            </a>
                        </div>
                        <!-- #logo end -->

                        <div class="header-misc">
                            <button id="search-button"
                                class="button button-rounded button-small side-panel-trigger ls-1 m-0 me-1 d-block d-lg-none"
                                title="Pesquisar imóveis">
                                <i class="uil uil-search p-0 m-0"></i>
                                <span>Buscar imóveis</span>
                            </button>
                        </div>

                        <div class="primary-menu-trigger">
                            <button class="cnvs-hamburger" type="button" title="Open Mobile Menu">
                                <span class="cnvs-hamburger-box"><span class="cnvs-hamburger-inner"></span></span>
                            </button>
                        </div>

                        <!-- Primary Navigation
                        ============================================= -->
                        <nav class="primary-menu style-3 with-arrows">
                            <ul class="menu-container">
                                <li class="menu-item {{ isset($page->cmsPost) && $page->cmsPost->slug === 'index' ? 'current' : '' }}">
                                    <a class="menu-link" href="{{ route('web.pgs.index') }}">
                                        <div>Home</div>
                                    </a>
                                </li>

                                <li
                                    class="menu-item {{ (isset($idxPage) && $idxPage->cmsPost->slug === 'a-venda') || (isset($page->cmsPost) && $page->cmsPost->slug === 'a-venda') ? 'current' : '' }}">
                                    <a class="menu-link"
                                        href="{{ route('web.real-estate.individuals.index', 'a-venda') }}">
                                        <div>Comprar</div>
                                    </a>
                                </li>

                                <li
                                    class="menu-item {{ (isset($idxPage) && $idxPage->cmsPost->slug === 'para-alugar') || (isset($page->cmsPost) && $page->cmsPost->slug === 'para-alugar') ? 'current' : '' }}">
                                    <a
                                        class="menu-link"
                                        href="{{ route('web.real-estate.individuals.index', 'para-alugar') }}">
                                        <div>Alugar</div>
                                    </a>
                                </li>

                                <li
                                    class="menu-item {{ (isset($idxPage) && $idxPage->cmsPost->slug === 'lancamentos') || (isset($page->cmsPost) && $page->cmsPost->slug === 'lancamentos') ? 'current' : '' }}">
                                    <a
                                        class="menu-link"
                                        href="{{ route('web.real-estate.enterprises.index') }}">
                                        <div>Lançamentos</div>
                                    </a>

                                    <ul class="sub-menu-container">
                                        <li class="menu-item">
                                            <a class="menu-link"
                                                href="{{ route('web.real-estate.enterprises.role', 'na-planta') }}">
                                                <div>Na planta</div>
                                            </a>
                                        </li>

                                        <li class="menu-item">
                                            <a class="menu-link"
                                                href="{{ route('web.real-estate.enterprises.role', 'em-construcao') }}">
                                                <div>Em construção</div>
                                            </a>
                                        </li>

                                        <li class="menu-item">
                                            <a class="menu-link"
                                                href="{{ route('web.real-estate.enterprises.role', 'pronto-para-morar') }}">
                                                <div>Pronto para morar</div>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="menu-item {{ isset($page->cmsPost) && $page->cmsPost->slug === 'anunciar' ? 'current' : '' }}">
                                    <a class="menu-link" href="#">
                                        <div>Anuncie</div>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <!-- #primary-menu end -->
                    </div>
                </div>
            </div>

            <div class="header-wrap-clone"></div>
        </header>
        <!-- #header end -->

        @yield('content')

        <!-- Footer
        ============================================= -->
        <footer id="footer" class="dark bg-color">
            <div class="container">
                <!-- Footer Widgets
                ============================================= -->
                <div class="footer-widgets-wrap py-5 z-9">
                    <div class="row col-mb-30">
                        <div class="col-lg-4 order-last order-lg-first">
                            <div class="widget text-center">
                                <img class="lazy" data-src="{{ asset('build/web/images/exho-logo-footer.png') }}"
                                    alt="{{ config('app.name') }}">

                                <div class="mt-4 text-center text-smaller ls-1">
                                    Copyrights &copy; {{ date('Y') > '2024' ? '2024 - ' . date('Y') : '2024' }} Todos
                                    os direitos reservados por {{ config('app.name') }}.
                                    {{ $webSettings['creci'] ?? '' }} {{ $webSettings['cnpj'] ?? '' }}
                                </div>

                                <div
                                    class="mt-3 d-flex align-items-center justify-content-center text-center text-smaller ls-1">
                                    <a href="#" target="_blank" class="d-flex align-items-center">
                                        <i class="me-2">Desenvolvido por:</i>
                                        <img class="lazy"
                                            data-src="{{ asset('build/web/images/desenvolvido-por-exho-estate-dark.png') }}"
                                            alt="Desenvolvido por ExhoTech" title="Desenvolvido por ExhoTech." />
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="row col-mb-30">
                                <div class="col-6 col-md-3">
                                    <div class="widget widget_links">
                                        <h4 class="">
                                            Imóveis
                                        </h4>

                                        <li class="text-smaller ls-1">
                                            <a href="{{ route('web.real-estate.individuals.index', 'a-venda') }}">
                                                <div>Comprar</div>
                                            </a>
                                        </li>

                                        <li class="text-smaller ls-1">
                                            <a href="{{ route('web.real-estate.individuals.index', 'para-alugar') }}">
                                                <div>Alugar</div>
                                            </a>
                                        </li>

                                        <li class="text-smaller ls-1">
                                            <a href="#">
                                                <div>Anunciar Imóvel</div>
                                            </a>
                                        </li>
                                    </div>

                                    <div class="widget widget_links mt-4">
                                        <h4 class="">
                                            Lançamentos
                                        </h4>

                                        <ul>
                                            <li class="text-smaller ls-1 d-none">
                                                <a href="{{ route('web.real-estate.enterprises.index') }}">
                                                    <div>Todos os lançamentos</div>
                                                </a>
                                            </li>

                                            <li class="text-smaller ls-1">
                                                <a href="{{ route('web.real-estate.enterprises.role', 'na-planta') }}">
                                                    <div>Na Planta</div>
                                                </a>
                                            </li>

                                            <li class="text-smaller ls-1">
                                                <a href="{{ route('web.real-estate.enterprises.role', 'em-construcao') }}">
                                                    <div>Em Construção</div>
                                                </a>
                                            </li>

                                            <li class="text-smaller ls-1">
                                                <a href="{{ route('web.real-estate.enterprises.role', 'pronto-para-morar') }}">
                                                    <div>Pronto para Morar</div>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-6 col-md-3">
                                    <div class="widget widget_links">
                                        <h4 class="">
                                            Institucional
                                        </h4>

                                        <ul>
                                            <li class="text-smaller ls-1">
                                                <a href="{{ route('web.pgs.about') }}">
                                                    <div>Quem Somos</div>
                                                </a>
                                            </li>

                                            <li class="text-smaller ls-1">
                                                <a href="{{ route('web.blog.index') }}">
                                                    <div>Blog e Materiais Grátis</div>
                                                </a>
                                            </li>

                                            <li class="text-smaller ls-1">
                                                <a href="{{ route('web.pgs.contact-us') }}">
                                                    <div>Fale Conosco</div>
                                                </a>
                                            </li>

                                            <li class="text-smaller ls-1">
                                                <a href="{{ route('web.pgs.work-with-us') }}">
                                                    <div>Trabalhe Conosco</div>
                                                </a>
                                            </li>

                                            <li class="text-smaller ls-1">
                                                <a href="{{ route('web.pgs.rules', 'politica-de-privacidade') }}">
                                                    Política de Privacidade
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="widget">
                                        <h4 class="">
                                            Mantenha Contato
                                        </h4>

                                        <ul class="list-unstyled">
                                            @if ($webSettings['whatsapp'][0]['link'])
                                                <li class="mb-3">
                                                    <a href="{{ $webSettings['whatsapp'][0]['link'] }}" target="_blank"
                                                        class="d-flex" title="Whatsapp">
                                                        <i class="me-3 bi-whatsapp"></i>
                                                        {!! $webSettings['whatsapp'][0]['phone'] !!}
                                                    </a>
                                                </li>
                                            @endif

                                            @if ($webSettings['mail'][0])
                                                <li class="mb-3">
                                                    <a href="mailto:{{ $webSettings['mail'][0] }}" class="d-flex">
                                                        <i class="me-3 bi-envelope-at"></i>
                                                        {{ $webSettings['mail'][0] }}
                                                    </a>
                                                </li>
                                            @endif

                                            @if ($webSettings['addresses'][0] && $webSettings['addresses'][0]['display_address'])
                                                <li class="mb-3">
                                                    <span class="d-flex text-muted">
                                                        <i class="me-3 bi-geo-alt"></i>
                                                        <span class="text-smaller ls-1">
                                                            {!! $webSettings['addresses'][0]['display_address'] !!}
                                                        </span>
                                                    </span>
                                                </li>
                                            @endif
                                        </ul>

                                        <div class="">
                                            @if ($webSettings['instagram'][0]['link'])
                                                @foreach ($webSettings['instagram'] as $instagram)
                                                    <a href="{{ $instagram['link'] }}" target="_blank"
                                                        class="social-icon si-small bg-instagram"
                                                        data-bs-container="body" data-bs-toggle="popover"
                                                        data-bs-trigger="hover focus" data-bs-placement="bottom"
                                                        data-bs-content="{{ $instagram['name'] ?? 'Instagram' }}">
                                                        <i class="fa-brands fa-instagram"></i>
                                                        <i class="fa-brands fa-instagram"></i>
                                                    </a>
                                                @endforeach
                                            @endif

                                            @if ($webSettings['addresses'][0]['gmaps_link'])
                                                <a href="{{ $webSettings['addresses'][0]['gmaps_link'] }}"
                                                    target="_blank" class="social-icon si-small bg-google" title="Mapa">
                                                    <i class="fa-solid fa-map-marked-alt"></i>
                                                    <i class="fa-solid fa-map-marked-alt"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- .footer-widgets-wrap end -->
            </div>
        </footer>
        <!-- #footer end -->
    </div>
    <!-- #wrapper end -->

    <!-- Go To Top
    ============================================= -->
    <div id="gotoTop" class="uil uil-angle-up"></div>

    @if ($webSettings['whatsapp'][0]['link'])
        <!-- Whatsapp Btn
        ============================================= -->
        <a href="{{ $webSettings['whatsapp'][0]['link'] }}" target="_blank" class="whatsapp-link">
            <div id="whatsapp-button" class="uil uil-whatsapp infinite animated tada slow"
                data-bs-container="body" data-bs-toggle="popover" data-bs-trigger="hover focus"
                data-bs-placement="left" data-bs-content="Fale conosco!"></div>
        </a>
    @endif

    @include('web.layouts._partials._gdr-alert')

    <!-- Javascripts
    ============================================= -->
    @if (config('app.g_tag'))
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('app.g_tag') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config("app.g_tag") }}');
        </script>
    @endif

    @if (config('app.g_recapcha_site'))
        <!-- Google Recaptcha -->
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('app.g_recapcha_site') }}"></script>
    @endif

    <script src="{{ asset('build/web/js/plugins.min.js') }}"></script>
    <script src="{{ asset('build/web/js/functions.bundle.js') }}"></script>

    <script src="{{ asset('build/web/js/selectsplitter.js') }}"></script>
    <script src="{{ asset('build/web/js/bs-select.js') }}"></script>

    @vite([
        'resources/js/web/global-custom.js',
        'resources/js/web/property-search.js'
    ])

    {{-- Scripts injected in pages --}}
    @yield('scripts')
</body>
</html>
