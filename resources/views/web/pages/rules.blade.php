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
            <div id="rules-section" class="section my-0 header-stick footer-stick">
                <div class="container">
                    <div class="row justify-content-center col-mb-30">
                        <div class="col-lg-12">
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

                            <div class="body-content ls-1">
                                {!! $page->body !!}
                            </div>
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
