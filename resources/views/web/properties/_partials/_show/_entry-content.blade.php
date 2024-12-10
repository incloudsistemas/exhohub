<!-- Entry Content
============================================= -->
<div class="entry-content mt-0">
    <div class="body-content mb-4">
        {!! $page->body !!}
    </div>
    <!-- Post Single - Content End -->

    <!-- Characteristics
    ============================================= -->
    @if ($characteristics['differences'] && count($characteristics['differences']))
        <h6 class="fs-6 fw-semibold mb-2">
            Diferenciais:
        </h6>

        <div class="tagcloud border-color mb-4">
            @foreach ($characteristics['differences'] as $differences)
                <a href="javascript:;" class="no-cursor color">
                    <i class="uil-check-circle"></i> {!! $differences !!}
                </a>
            @endforeach
        </div>
    @endif

    @if ($characteristics['leisure'] && count($characteristics['leisure']))
        <h6 class="fs-6 fw-semibold mb-2">
            Lazer e Esportes:
        </h6>

        <div class="tagcloud border-color mb-4">
            @foreach ($characteristics['leisure'] as $leisure)
                <a href="javascript:;" class="no-cursor color">
                    <i class="uil-check-circle"></i> {!! $leisure !!}
                </a>
            @endforeach
        </div>
    @endif

    @if ($characteristics['security'] && count($characteristics['security']))
        <h6 class="fs-6 fw-semibold mb-2">
            Segurança:
        </h6>

        <div class="tagcloud border-color mb-4">
            @foreach ($characteristics['security'] as $security)
                <a href="javascript:;" class="no-cursor color">
                    <i class="uil-check-circle"></i> {!! $security !!}
                </a>
            @endforeach
        </div>
    @endif

    @if ($characteristics['infrastructure'] && count($characteristics['infrastructure']))
        <h6 class="fs-6 fw-semibold mb-2">
            Comodidades e Serviços:
        </h6>

        <div class="tagcloud border-color mb-4">
            @foreach ($characteristics['infrastructure'] as $infrastructure)
                <a href="javascript:;" class="no-cursor color">
                    <i class="uil-check-circle"></i> {!! $infrastructure !!}
                </a>
            @endforeach
        </div>
    @endif
    <!-- .characteristics end -->

    @if ($page->show_address !== 0)
        <!-- G MAPS
        ============================================= -->
        <div id="g-maps" class="mb-4">
            <h3 class="mb-0">
                Conheça a região {{ in_array($page->show_address, [1, 2]) && !empty($page->address->district) ? "do bairro {$page->address->district}" : '' }}
            </h3>

            <p class="text-smaller text-muted ls-1 mb-4">
                {{ $page->display_web_address }}
            </p>

            <div class="static-map vh-75 lazy"
                data-bg="{{ asset('build/web/images/static-map.jpg') }}">
                <a
                    class="button button-rounded h-bg-secondary ls-1 mx-0 position-absolute bottom-0 start-50 translate-middle-x"
                    style="margin-bottom: 50px;">
                    <i class="fa-solid fa-map-marked-alt"></i>
                    <span>Explore o mapa</span>
                </a>
            </div>

            {{-- gmap --}}
            <div class="display-gmap vh-75" data-address="{{ $page->display_web_address }}"
                data-maptype="ROADMAP" data-zoom="15" data-scrollwheel="false"
                data-control-zoom="true"
                data-markers='[{address: "{{ $page->display_web_address }}",html: "<h6 class=mb-1>{{ $page->title }}</h6><p class=mb-0>{{ $page->display_web_address }}</p>"}]'
                data-icon='{image: "{{ asset('build/web/images/marker.png') }}", iconsize: [65, 65], iconanchor: [14,44]}'>
            </div>
        </div>
        <!-- .g-maps end -->
    @endif

    @if (isset($page->tags) && count($page->tags))
        <!-- Tag Cloud
        ============================================= -->
        <div class="tagcloud mb-4">
            @foreach ($page->tags as $tag)
                <a href="javascript:;" class="no-cursor">
                    {!! $tag !!}
                </a>
            @endforeach
        </div>
        <!-- .tagcloud end -->
    @endif

    <!-- Post Single - Share
    ============================================= -->
    <div id="cnvs-article-share" class="card border-default my-4">
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between">
                <h6 class="fs-6 fw-semibold mb-0">
                    Compartilhe este imóvel:
                </h6>

                <div class="d-flex">
                    <a href="https://www.facebook.com/sharer.php?u={{ urlencode(url()->current()) }}"
                        class="social-icon si-small text-white border-transparent rounded-circle bg-facebook"
                        title="Facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>

                    <a href="https://twitter.com/share?url={{ urlencode(url()->current()) }}"
                        class="social-icon si-small text-white border-transparent rounded-circle bg-twitter"
                        title="Twitter">
                        <i class="fa-brands fa-twitter"></i>
                        <i class="fa-brands fa-twitter"></i>
                    </a>

                    <a href="http://pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}"
                        class="social-icon si-small text-white border-transparent rounded-circle bg-pinterest"
                        title="Pinterest">
                        <i class="fa-brands fa-pinterest-p"></i>
                        <i class="fa-brands fa-pinterest-p"></i>
                    </a>

                    <a href="https://api.whatsapp.com/send?text={{ urlencode(url()->current()) }}"
                        class="social-icon si-small text-white border-transparent rounded-circle bg-whatsapp"
                        title="Whatsapp">
                        <i class="fa-brands fa-whatsapp"></i>
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>

                    {{-- <a href="#"
                        class="social-icon si-small text-white border-transparent rounded-circle bg-rss"
                        title="RSS">
                        <i class="fa-solid fa-rss"></i>
                        <i class="fa-solid fa-rss"></i>
                    </a> --}}

                    <a href="mailto:?body={{ urlencode(url()->current()) }}"
                        class="social-icon si-small text-white border-transparent rounded-circle bg-email3 me-0"
                        title="Mail">
                        <i class="fa-solid fa-envelope"></i>
                        <i class="fa-solid fa-envelope"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Post Single - Share End -->
</div>
<!-- .entry-content end -->
