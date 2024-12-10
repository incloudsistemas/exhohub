@if ($page->propertable->featured_image && $galleryImages->count() === 0)
    <!-- Entry Image
    ============================================= -->
    <div class="entry-image" data-lightbox="gallery">
        <a class="grid-item"
            href="{{ CreateThumb(src: $page->propertable->featured_image->getUrl(), width: 1024, height: 768, watermark: $page->has_watermark, watermarkPosition: $page->display_watermark_position) }}"
            data-lightbox="gallery-item">
            <img class="lazy"
                data-src="{{ CreateThumb(src: $page->propertable->featured_image->getUrl(), width: 800, height: 600, watermark: $page->has_watermark, watermarkPosition: $page->display_watermark_position) }}"
                alt="{{ $page->title }}">
        </a>
    </div>
    <!-- .entry-image end -->
@endif
