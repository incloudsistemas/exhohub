@component('mail::message')
# Nova conversão registrada

**Nome:** {{ $data['name'] }}

**E-mail:** {{ $data['email'] }}

@if (isset($data['phone']) && !empty($data['phone']))
**Telefone:** {{ $data['phone'] }}
@endif

@if (isset($data['message']) && !empty($data['message']))
{{ $data['message'] }}
@endif
@endcomponent
