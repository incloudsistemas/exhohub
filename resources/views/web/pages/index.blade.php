@extends('web.layouts.default')

{{-- Stylesheets Section --}}
@section('styles')
    @vite('resources/web-assets/css/swiper.css')
@endsection

{{-- Content --}}
@section('content')
    @include('web.pages._partials._index._slider')

    <!-- Content
    ============================================= -->
    <section id="content">
        <div class="content-wrap">
            <div class="header-stick footer-stick">
                @include('web.pages._partials._index._properties')
            </div>
        </div>
    </section>
    <!-- #content end -->
@endsection

{{-- Scripts Section --}}
@section('scripts')
@endsection
