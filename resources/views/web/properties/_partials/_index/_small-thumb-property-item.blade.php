<div class="property-item small-thumb-property-item entry col-12">
    <div class="grid-inner border shadow-sm rounded-6 row g-0">
        <div class="col-md-5">
            @include('web.properties._partials._index._entry-image')
        </div>

        <div class="col-md-7">
            @include('web.properties._partials._index._entry-body', [
                'limitChars' => 43
            ])
        </div>
    </div>
</div>
