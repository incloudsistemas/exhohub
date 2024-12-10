@extends('web.layouts.default')

{{-- Stylesheets Section --}}
@section('styles')
@endsection

{{-- Content --}}
@section('content')
    <!-- Content
    ============================================= -->
    <section id="content">
        <div class="content-wrap">
            <div id="about-section" class="section my-0 header-stick footer-stick">
                <div class="container">
                    <div class="row justify-content-center col-mb-30">
                        <div class="col-lg-7">
                            <!-- Page Title
                            ============================================= -->
                            @component('web.layouts._partials._page-title', [
                                'title'    => $page->cmsPost->subtitle ?? $page->cmsPost->title,
                                'subtitle' => $page->cmsPost->excerpt,
                            ])
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span>{!! $page->cmsPost->title !!}</span>
                                </li>
                            @endcomponent
                            <!-- .pagetitle end -->

                            <div class="body-content">
                                {!! $page->cmsPost->body !!}
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <img class="rounded-6 lazy"
                                data-src="{{ PlaceholderImg(width: 800, height: 600) }}"
                                alt="{{ $page->cmsPost->title }}">
                        </div>
                    </div>
                </div>
            </div>
    </section>
    <!-- #content end -->
@endsection

{{-- Scripts Section --}}
@section('scripts')
@endsection
