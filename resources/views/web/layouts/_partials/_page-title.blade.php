<nav aria-label="breadcrumb">
    <ol class="breadcrumb text-start text-smaller ls-1">
        <li class="breadcrumb-item">
            <a href="{{ route('web.pgs.index') }}">
                <i class="uil uil-home"></i>
                <span>Home</span>
            </a>
        </li>

        {{ $slot }}
    </ol>
</nav>

<div class="heading-block {{ $classes ?? '' }}">
    <h1 class="color h2">
        {!! $title !!}
    </h1>

    @if (isset($subtitle))
        <span>
            {!! $subtitle !!}
        </span>
    @endif
</div>
