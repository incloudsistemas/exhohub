<div class="entry-image mb-0">
    <div class="fslider" data-arrows="true" data-pagi="true">
        <div class="flexslider">
            <div class="slider-wrap">
                @forelse ($property->propertable->gallery_images->take(4) as $key => $image)
                    <div class="slide">
                        <a href="{{ $route ?? '#' }}">
                            <img class="lazy"
                                data-src="{{ CreateThumb(src: $image->getUrl(), width: $mediaWidth ?? 310, height: $mediaHeigth ?? 275, watermark: $property->has_watermark, watermarkPosition: $property->display_watermark_position) }}"
                                alt="{{ $image->name ?? $property->title }}" width="{{ $mediaWidth ?? 310 }}"
                                height="{{ $mediaHeigth ?? 275 }}">
                        </a>
                    </div>
                @empty
                    <a href="{{ $route ?? '#' }}">
                        <img class="lazy"
                            data-src="{{ PlaceholderImg(width: $mediaWidth ?? 310, height: $mediaHeigth ?? 275) }}"
                            alt="{{ $property->title }}" width="{{ $mediaWidth ?? 310 }}"
                            height="{{ $mediaHeigth ?? 275 }}">
                    </a>
                @endforelse
            </div>
        </div>
    </div>

    <div class="entry-categories bg-transparent p-2">
        @if ($property->propertable_type === 'real_estate_individuals')
            <span class="badge bg-secondary h-bg-color ls-1 custom-link"
                data-href="{{ route('web.real-estate.individuals.usage-type', [$property->propertable->display_role_slug, strtolower($property->display_usage), $property->type->slug]) }}">
                {{ $property->type->name }}
            </span>

            <span class="badge bg-dark">
                {{ $property->code }}
            </span>
        @else
            <span class="badge bg-secondary h-bg-color ls-1 custom-link"
                data-href="{{ route('web.real-estate.enterprises.usage-type', [strtolower($property->display_usage), $property->type->slug]) }}">
                {{ $property->type->name }}
            </span>

            <span class="badge bg-color h-bg-secondary ls-1 custom-link"
                data-href="{{ route('web.real-estate.enterprises.role', $property->propertable->display_role_slug) }}">
                {{ $property->propertable->display_role }}
            </span>
        @endif
    </div>
</div>
