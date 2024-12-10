@if (!$agent->isMobile())
    <!-- Sidebar
    ============================================= -->
    <aside class="col-lg-4 sidebar sticky-sidebar-wrap" data-offset-top="0">
        <div class="sidebar-widgets-wrap">
            <div class="sticky-sidebar">
                <div class="widget">
                    <div class="card border-1 p-2 rounded-6 shadow-lg">
                        <div class="card-body">
                            @include('web.properties._partials._property-search-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </aside>
    <!-- .sidebar end -->
@endif
