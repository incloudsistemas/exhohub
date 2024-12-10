@include('web.layouts._partials._form-alert')

<form method="get" action="{{ route('web.real-estate.properties.search') }}" id="property-search-form"
    class="mb-0 not-dark">
    <div class="row">
        @if (isset($page->cmsPost) && in_array($page->cmsPost->slug, ['a-venda', 'para-alugar', 'lancamentos']))
            <input type="hidden" name="role"
                value="{{ isset($role) && in_array($role, ['a-venda', 'para-alugar']) ? $role : 'lancamentos' }}">
        @else
            <div class="col-lg-12 form-group">
                <label for="property-search-role" class="text-smaller ls-1">
                    O que você procura?
                </label>

                <select name="role" id="property-search-role" class="selectpicker form-control">
                    <option value="a-venda">
                        Imóveis à venda
                    </option>

                    <option value="para-alugar">
                        Imóveis para alugar
                    </option>

                    <option value="lancamentos">
                        Lançamentos
                    </option>
                </select>
            </div>
        @endif

        <div class="col-lg-12 form-group">
            <label for="property-search-location" class="text-smaller ls-1">
                Onde deseja morar?
            </label>

            <input type="text" name="location" id="property-search-location" class="form-control"
                placeholder="Condomínio, rua, bairro ou cidade" value="{{ $data['location'] ?? old('location') }}">
        </div>

        <div class="col-lg-12 form-group">
            <label for="property-search-types" class="text-smaller ls-1">
                Tipo do imóvel
            </label>

            <select multiple name="types[]" id="property-search-types" class="selectpicker form-control"
                data-actions-box="true" data-none-selected-text="Todos os imóveis"
                data-select-all-text="Selecionar todos" data-deselect-all-text="Remover todos">
                <optgroup label="Residencial">
                    @foreach ($propertyTypes['residencial'] as $key => $residencialType)
                        <option value="{{ $key }}_residencial"
                            {{ is_array(old('types', isset($data['types']) ? $data['types'] : [])) && in_array("{$key}_residencial", old('types', isset($data['types']) ? $data['types'] : [])) ? 'selected=selected' : '' }}>
                            {{ $residencialType }}
                        </option>
                    @endforeach
                </optgroup>

                <optgroup label="Comercial">
                    @foreach ($propertyTypes['comercial'] as $key => $comercialType)
                        <option value="{{ $key }}_comercial"
                            {{ is_array(old('types', isset($data['types']) ? $data['types'] : [])) && in_array("{$key}_comercial", old('types', isset($data['types']) ? $data['types'] : [])) ? 'selected=selected' : '' }}>
                            {{ $comercialType }}
                        </option>
                    @endforeach
                </optgroup>
            </select>
        </div>

        <div class="col-lg-12 form-group" data-element="enterprise-section">
            <label for="property-search-enterprise-role" class="text-smaller ls-1">
                Estágio do lançamento
            </label>

            <select name="enterprise_role" id="property-search-enterprise-role" class="selectpicker form-control">
                <option value="">
                    Todos os imóveis
                </option>

                @foreach ($enterpriseRoles as $key => $enterpriseRole)
                    <option value="{{ $key }}"
                        {{ old('enterprise_role') == $key || (isset($idxRole) && $idxRole == $key) || (isset($data['enterprise_role']) && $data['enterprise_role'] == $key) ? 'selected=selected' : '' }}>
                        {{ $enterpriseRole }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-12 form-group">
            <label for="property-search-price" class="ls-1 mb-0">
                Preço (R$)
            </label>

            <div class="row">
                <div class="col-lg-6 form-group mb-0 pe-lg-1">
                    <label for="property-search-min-price" class="text-smaller ls-1">
                        Mínimo
                    </label>

                    <input type="text" name="min_price" id="property-search-min-price"
                        class="form-control float_ptbr_mask" value="{{ $data['min_price'] ?? old('min_price') }}">
                </div>

                <div class="col-lg-6 form-group mb-0 ps-lg-1">
                    <label for="property-search-max-price" class="text-smaller ls-1">
                        Máximo
                    </label>

                    <input type="text" name="max_price" id="property-search-max-price"
                        class="form-control float_ptbr_mask" value="{{ $data['max_price'] ?? old('max_price') }}">
                </div>
            </div>
        </div>

        <div class="col-lg-12 form-group">
            <label for="property-search-bedroom" class="ls-1">
                Quartos
            </label>
            <br />

            <div class="btn-group flex-wrap">
                @for ($i = 1; $i <= 4; $i++)
                    <input type="radio" name="bedroom" class="btn-check"
                        id="property-search-bedroom-{{ $i }}" autocomplete="off"
                        value="{{ $i }}"
                        {{ old('bedroom') == $i || (isset($data['bedroom']) && $data['bedroom'] == $i) ? 'checked=checked data-checked=true' : '' }}>
                    <label for="property-search-bedroom-{{ $i }}"
                        class="btn btn-outline-secondary px-3 fw-semibold ls-0 text-transform-none">
                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}{{ $i == 4 ? '+' : '' }}
                    </label>
                @endfor
            </div>
        </div>

        <div class="col-lg-12 form-group">
            <label for="property-search-bathroom" class="ls-1">
                Banheiros
            </label>
            <br />

            <div class="btn-group flex-wrap">
                @for ($i = 1; $i <= 4; $i++)
                    <input type="radio" name="bathroom" class="btn-check"
                        id="property-search-bathroom-{{ $i }}" autocomplete="off"
                        value="{{ $i }}"
                        {{ old('bathroom') == $i || (isset($data['bathroom']) && $data['bathroom'] == $i) ? 'checked=checked data-checked=true' : '' }}>
                    <label for="property-search-bathroom-{{ $i }}"
                        class="btn btn-outline-secondary px-3 fw-semibold ls-0 text-transform-none">
                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}{{ $i == 4 ? '+' : '' }}
                    </label>
                @endfor
            </div>
        </div>

        <div class="col-lg-12 form-group">
            <label for="property-search-garage" class="ls-1">
                Vagas
            </label>
            <br />

            <div class="btn-group flex-wrap">
                @for ($i = 1; $i <= 4; $i++)
                    <input type="radio" name="garage" class="btn-check"
                        id="property-search-garage-{{ $i }}" autocomplete="off"
                        value="{{ $i }}"
                        {{ old('garage') == $i || (isset($data['garage']) && $data['garage'] == $i) ? 'checked=checked data-checked=true' : '' }}>
                    <label for="property-search-garage-{{ $i }}"
                        class="btn btn-outline-secondary px-3 fw-semibold ls-0 text-transform-none">
                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}{{ $i == 4 ? '+' : '' }}
                    </label>
                @endfor
            </div>
        </div>

        <div class="col-lg-12 form-group">
            <label for="property-search-useful_area" class="ls-1 mb-0">
                Área útil (m²)
            </label>

            <div class="row">
                <div class="col-lg-6 form-group mb-0 pe-lg-1">
                    <label for="property-search-min-useful_area" class="text-smaller ls-1">
                        Mínimo
                    </label>

                    <input type="text" name="min_useful_area" id="property-search-min-useful_area"
                        class="form-control float_ptbr_mask"
                        value="{{ $data['min_useful_area'] ?? old('min_useful_area') }}">
                </div>

                <div class="col-lg-6 form-group mb-0 ps-lg-1">
                    <label for="property-search-max-useful_area" class="text-smaller ls-1">
                        Máximo
                    </label>

                    <input type="text" name="max_useful_area" id="property-search-max-useful_area"
                        class="form-control float_ptbr_mask"
                        value="{{ $data['max_useful_area'] ?? old('max_useful_area') }}">
                </div>
            </div>
        </div>

        <div class="col-lg-12 form-group">
            <label for="property-search-code" class="ls-1">
                Código
            </label>

            <input type="text" name="code" id="property-search-code" class="form-control"
                placeholder="Digite o código do imóvel" value="{{ $data['code'] ?? old('code') }}">
        </div>

        <div class="col-lg-12 form-group d-none">
            <input type="text" name="property-search-botcheck" id="property-search-botcheck"
                class="form-control">
        </div>

        <div class="col-lg-12 form-group mb-0">
            <button type="submit" id="property-search-submit"
                class="button button-rounded ls-1 m-0 w-100">
                <i class="uil-search"></i>
                <span class="">Buscar</span>
            </button>

            @if (isset($page->cmsPost) && in_array($page->cmsPost->slug, ['a-venda', 'para-alugar', 'lancamentos']))
                <a href="{{ isset($role) && in_array($role, ['a-venda', 'para-alugar']) ? route('web.real-estate.individuals.index', $role) : route('web.real-estate.enterprises.index') }}"
                    class="btn btn-sm btn-link text-muted fw-bold p-0 h-text-color text-start mt-3">
                    <i class="uil-times"></i>
                    <u>Limpar filtros</u>
                </a>
            @endif
        </div>
    </div>

    @if (config('app.g_recapcha_site'))
        <input type="hidden" class="g-recaptcha-site" value="{{ config('app.g_recapcha_site') }}">
        <input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" value="">
    @endif

    <input type="hidden" name="prefix" value="property-search-">
    @csrf
</form>
