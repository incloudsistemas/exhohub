<!-- Sidebar
============================================= -->
<aside class="col-lg-4 sidebar sticky-sidebar-wrap" data-offset-top="0">
    <div class="sidebar-widgets-wrap">
        <div class="sticky-sidebar">
            <div class="widget">
                <div class="card border-1 p-2 rounded-6 shadow-lg">
                    <div class="card-body">
                        <div class="heading-block border-0 mb-2">
                            <h5 class="">
                                Que tal ter condições exclusivas para aquirir o seu?
                            </h5>
                        </div>

                        @include('web.layouts._partials._form-alert')

                        <form method="post" action="{{ route('web.business.lead') }}" id="business-lead-form"
                            class="mb-0 not-dark">
                            @csrf

                            <input type="hidden" name="property_code" value="{{ $page->code }}">

                            <div class="row">
                                <div class="col-lg-12 form-group">
                                    <label for="business-lead-name" class="text-smaller ls-1">
                                        <small>*</small> Nome
                                    </label>

                                    <input type="text" name="name" id="business-lead-name" class="form-control"
                                        value="{{ old('name') }}">
                                </div>

                                <div class="col-lg-12 form-group">
                                    <label for="business-lead-email" class="text-smaller ls-1">
                                        <small>*</small> Email
                                    </label>

                                    <input type="email" name="email" id="business-lead-email" class="form-control"
                                        value="{{ old('email') }}">
                                </div>

                                <div class="col-lg-12 form-group">
                                    <label for="business-lead-phone" class="text-smaller ls-1">
                                        <small>*</small> Telefone para contato
                                    </label>

                                    <input type="text" name="phone" id="business-lead-phone"
                                        class="form-control phone_ptbr_mask" value="{{ old('phone') }}">
                                </div>

                                <div class="col-lg-12 form-group">
                                    <label for="business-lead-message" class="text-smaller ls-1">
                                        Mensagem
                                    </label>

                                    <textarea name="message" id="business-lead-message" class="form-control"
                                        placeholder="Escreva as sua mensagem..." rows="4" cols="30">{{ "Olá, gostaria de ter mais informações para comprar: {$page->title}, que encontrei no site. Aguardo seu contato, obrigado." ?? old('message') }}</textarea>
                                </div>

                                <div class="col-lg-12 form-group">
                                    <div class="form-check mb-0">
                                        <input type="checkbox" id="business-lead-rule"
                                            class="form-check-input rule-check" value="1">
                                        <label class="form-check-label text-smaller ls-1" for="business-lead-rule">
                                            Estou ciente e aceito a <span><a href="{{ route('web.pgs.rules', 'politica-de-privacidade') }}" target="_blank">política de privacidade</a></span>.
                                        </label>
                                    </div>
                                </div>

                                <div class="col-lg-12 form-group d-none">
                                    <input type="text" name="business-lead-botcheck" id="business-lead-botcheck"
                                        class="form-control">
                                </div>

                                <div class="col-lg-12 form-group mb-0">
                                    <button type="submit" id="business-lead-submit"
                                        class="button button-rounded ls-1 m-0 w-100" data-class="down-sm:button-small"
                                        data-form-action="submit">
                                        <div class="indicator-label">
                                            <i class="uil uil-navigator"></i>
                                            <span>Enviar mensagem</span>
                                        </div>
                                        <div class="indicator-progress">
                                            Por favor, aguarde... <span class="align-middle spinner-border spinner-border-sm ms-2"></span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            @if (config('app.g_recapcha_site'))
                                <input type="hidden" class="g-recaptcha-site" value="{{ config('app.g_recapcha_site') }}">
                                <input type="hidden" name="g-recaptcha-response" class="g-recaptcha-response" value="">
                            @endif

                            <input type="hidden" name="prefix" value="business-lead-">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>
<!-- .sidebar end -->
