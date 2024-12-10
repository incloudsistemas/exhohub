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
            <div class="section m-0 header-stick footer-stick">
                <div class="container">
                    <div class="row justify-content-center col-mb-30">
                        <!-- Post Content
                        ============================================= -->
                        <main class="postcontent col-lg-8 order-lg-last">
                            <div class="row d-flex align-items-center">
                                <div class="col-lg-9">
                                    <!-- Page Title
                                    ============================================= -->
                                    @component('web.layouts._partials._page-title', [
                                        'title'    => $page->cmsPost->title,
                                        'subtitle' => $properties->firstItem()
                                            ? "Exibindo {$properties->firstItem()} a {$properties->lastItem()} de {$properties->total()} resultados."
                                            : 'Nenhum registro encontrado.',
                                    ])
                                        @if (in_array($page->cmsPost->slug, ['a-venda', 'para-alugar']))
                                            @if (!isset($type))
                                                <li class="breadcrumb-item active" aria-current="page">
                                                    <span>{!! $page->cmsPost->title !!}</span>
                                                </li>
                                            @else
                                                <li class="breadcrumb-item">
                                                    <a href="{{ route('web.real-estate.individuals.index', $role) }}">
                                                        <span>{!! $page->cmsPost->title !!}</span>
                                                    </a>
                                                </li>
                                            @endif
                                        @else
                                            @if (!isset($role) && !isset($type))
                                                <li class="breadcrumb-item active" aria-current="page">
                                                    <span>{!! $page->cmsPost->title !!}</span>
                                                </li>
                                            @else
                                                <li class="breadcrumb-item">
                                                    <a href="{{ route('web.real-estate.enterprises.index') }}">
                                                        <span>{!! $page->cmsPost->title !!}</span>
                                                    </a>
                                                </li>

                                                @if (isset($role))
                                                    <li class="breadcrumb-item active" aria-current="page">
                                                        <span>{{ $displayRole }}</span>
                                                    </li>
                                                @endif
                                            @endif
                                        @endif

                                        @if (isset($usage))
                                            <li class="breadcrumb-item active" aria-current="page">
                                                <span>
                                                    {!! ucfirst($usage) !!}
                                                </span>
                                            </li>
                                        @endif

                                        @if (isset($type))
                                            <li class="breadcrumb-item active" aria-current="page">
                                                <span>
                                                    {!! $type->name !!}
                                                </span>
                                            </li>
                                        @endif
                                    @endcomponent
                                    <!-- .pagetitle end -->
                                </div>

                                <div class="col-lg-3 form-group">
                                    <select name="sort_by" id="property-sort-by" class="selectpicker form-control">
                                        <option value="">
                                            Classificar por:
                                        </option>
                                        <option value="publish-desc">
                                            Mais recente
                                        </option>
                                        <option value="publish-asc">
                                            Mais antigo
                                        </option>
                                        <option value="price-asc">
                                            Menor preço
                                        </option>
                                        <option value="price-desc">
                                            Maior preço
                                        </option>
                                    </select>
                                </div>
                            </div>

                            @unless ($properties->isEmpty())
                                <!-- Posts
                                ============================================= -->
                                <div id="properties-posts" class="row">
                                    @foreach ($properties as $key => $property)
                                        @if ($agent->isMobile())
                                            @include('web.properties._partials._index._property-item', [
                                                'route' => route('web.real-estate.properties.show', [
                                                    $property->slug,
                                                    $property->code,
                                                ]),
                                            ])
                                        @else
                                            @include('web.properties._partials._index._small-thumb-property-item', [
                                                'route' => route('web.real-estate.properties.show', [
                                                    $property->slug,
                                                    $property->code,
                                                ]),
                                            ])
                                        @endif
                                    @endforeach
                                </div>
                                <!-- #posts end -->

                                @if ($properties->hasPages())
                                    <!-- Pager
                                    ============================================= -->
                                    <div class="d-flex justify-content-between">
                                        @if (!isset($data['role']))
                                            {!! $properties->links() !!}
                                        @else
                                            {!! $properties->appends([
                                                'role'            => $data['role'] ?? '',
                                                'enterprise_role' => $data['enterprise_role'] ?? '',
                                                'enterprise_name' => $data['enterprise_name'] ?? '',
                                                'types'           => $data['types'] ?? '',
                                                'location'        => $data['location'] ?? '',
                                                'min_price'       => $data['min_price'] ?? '',
                                                'max_price'       => $data['max_price'] ?? '',
                                                'min_useful_area' => $data['min_useful_area'] ?? '',
                                                'max_useful_area' => $data['max_useful_area'] ?? '',
                                                'min_total_area'  => $data['min_total_area'] ?? '',
                                                'max_total_area'  => $data['max_total_area'] ?? '',
                                                'bedroom'         => $data['bedroom'] ?? '',
                                                'suite'           => $data['suite'] ?? '',
                                                'bathroom'        => $data['bathroom'] ?? '',
                                                'garage'          => $data['garage'] ?? '',
                                            ])->links() !!}
                                        @endif
                                    </div>
                                    <!-- .pager end -->
                                @endif
                            @endunless
                        </main>
                        <!-- .postcontent end -->

                        @include('web.properties._partials._index._sidebar-search-widget')
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
