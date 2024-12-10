@component('mail::message')
# Contato via website

**Página:** {{ $data['page'] }}

**Nome:** {{ $data['name'] }}

**E-mail:** {{ $data['email'] }}

@if (isset($data['phone']) && !empty($data['phone']))
**Telefone:** {{ $data['phone'] }}
@endif

@if (isset($data['subject']) && !empty($data['subject']))
**Assunto:** {{ $data['subject'] }}
@endif

@if (isset($data['message']) && !empty($data['message']))
{{ $data['message'] }}
@endif
@endcomponent
