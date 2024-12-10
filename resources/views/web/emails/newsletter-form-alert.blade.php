@component('mail::message')
# Nova inscrição na newsletter

**Página:** {{ $data['page'] }}

**Nome:** {{ $data['name'] }}

**E-mail:** {{ $data['email'] }}

@if (isset($data['phone']) && !empty($data['phone']))
**Telefone:** {{ $data['phone'] }}
@endif
@endcomponent
