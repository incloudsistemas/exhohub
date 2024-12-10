@if ($galleryImages->count() > 0 || $galleryVideos->count() > 0)
    <!-- Slider
    ============================================= -->
    <section id="slider" class="slider-element swiper_wrapper vh-50 vh-md-60">
        <div class="slider-inner">
            <div class="swiper swiper-parent">
                <div class="swiper-wrapper" data-lightbox="gallery">
                    <div class="swiper-slide">
                        <a href="{{ CreateThumb(src: $page->propertable->featured_image->getUrl(), width: 1024, height: 768, watermark: $page->has_watermark, watermarkPosition: $page->display_watermark_position) }}"
                            data-lightbox="gallery-item">
                            <div class="bg-overlay">
                                <div class="bg-overlay-content dark">
                                    <span class="overlay-trigger-icon op-ts op-0 bg-light text-dark"
                                        data-hover-animate="op-07" data-hover-animate-out="op-0">
                                        <i class="bi-image"></i>
                                    </span>
                                </div>

                                <div
                                    class="d-none bg-overlay-content text-overlay-mask dark align-items-end justify-content-start">
                                    <h4 class="mb-0">
                                        {!! $page->propertable->featured_image->name ?? $page->title !!}
                                    </h4>
                                </div>
                            </div>
                        </a>

                        <div class="swiper-slide-bg lazy"
                            data-bg="{{ CreateThumb(src: $page->propertable->featured_image->getUrl(), width: 450, height: 365, watermark: $page->has_watermark, watermarkPosition: $page->display_watermark_position) }}"
                            style="background-position: center center; background-size: cover; background-repeat: no-repeat;">
                        </div>
                    </div>

                    @if ($galleryVideos->count() > 0)
                        <div class="swiper-slide">
                            <div class="swiper swiper-nested-1">
                                <div class="swiper-wrapper">
                                    {{-- <div class="swiper-slide">
                                        <a href="https://www.youtube.com/watch?v={{ $page->embed_video }}"
                                            data-lightbox="iframe">
                                            <div class="bg-overlay">
                                                <div class="bg-overlay-content dark">
                                                    <span
                                                        class="overlay-trigger-icon op-ts op-07 bg-light text-dark"
                                                        data-hover-animate="op-1" data-hover-animate-out="op-07">
                                                        <i class="bi-play"></i>
                                                    </span>
                                                </div>

                                                <div
                                                    class="d-none bg-overlay-content text-overlay-mask dark align-items-end justify-content-start">
                                                    <h4 class="mb-0">
                                                        {!! $page->propertable->featured_image->name ?? $page->title !!}
                                                    </h4>
                                                </div>
                                            </div>
                                        </a>

                                        <div class="swiper-slide-bg lazy"
                                            data-bg="https://img.youtube.com/vi/{{ $page->embed_video }}/0.jpg"
                                            style="background-position: center center; background-size: cover; background-repeat: no-repeat;">
                                        </div>
                                    </div> --}}

                                    @foreach ($galleryVideos as $video)
                                        <div class="swiper-slide">
                                            <a href="{{ $video->getUrl() }}" data-lightbox="iframe">
                                                <div class="bg-overlay">
                                                    <div class="bg-overlay-content dark">
                                                        <span
                                                            class="overlay-trigger-icon op-ts op-07 bg-light text-dark"
                                                            data-hover-animate="op-1" data-hover-animate-out="op-07">
                                                            <i class="bi-play"></i>
                                                        </span>
                                                    </div>

                                                    <div
                                                        class="d-none bg-overlay-content text-overlay-mask dark align-items-end justify-content-start">
                                                        <h4 class="mb-0">
                                                            {!! $video->name ?? $page->title !!}
                                                        </h4>
                                                    </div>
                                                </div>
                                            </a>

                                            <iframe width="450" height="365" class="lazy"
                                                data-src="{{ $video->getUrl() }}"
                                                title="{{ $video->name ?? $page->title }}" allowfullscreen></iframe>
                                        </div>
                                    @endforeach
                                </div>

                                <div id="sw1-arrow-top" class="slider-arrow-top-sm">
                                    <i class="fa-solid fa-chevron-up"></i>
                                </div>

                                <div id="sw1-arrow-bottom" class="slider-arrow-bottom-sm">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>
                    @endif

                    @foreach ($galleryImages->slice(1) as $image)
                        <div class="swiper-slide">
                            <a href="{{ CreateThumb(src: $image->getUrl(), width: 1024, height: 768, watermark: $page->has_watermark, watermarkPosition: $page->display_watermark_position) }}"
                                data-lightbox="gallery-item">
                                <div class="bg-overlay">
                                    <div class="bg-overlay-content dark">
                                        <span class="overlay-trigger-icon op-ts op-0 bg-light text-dark"
                                            data-hover-animate="op-07" data-hover-animate-out="op-0">
                                            <i class="bi-image"></i>
                                        </span>
                                    </div>

                                    <div
                                        class="d-none bg-overlay-content text-overlay-mask dark align-items-end justify-content-start">
                                        <h4 class="mb-0">
                                            {!! $image->name ?? $page->title !!}
                                        </h4>
                                    </div>
                                </div>
                            </a>

                            <div class="swiper-slide-bg lazy"
                                data-bg="{{ CreateThumb(src: $image->getUrl(), width: 450, height: 365, watermark: $page->has_watermark, watermarkPosition: $page->display_watermark_position) }}"
                                style="background-position: center center; background-size: cover; background-repeat: no-repeat;">
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="slider-arrow-left">
                    <i class="uil uil-angle-left-b"></i>
                </div>

                <div class="slider-arrow-right">
                    <i class="uil uil-angle-right-b"></i>
                </div>
            </div>
        </div>

        <span class="custom-one-page-arrow">
            <i class="bi-hand-index-thumb text-light infinite slow animated fadeInRight"></i>
        </span>
    </section>
    <!-- #slider -->
@endif
