@component('mail::message')
# Novo anúncio de imóvel registrado

**Página:** {{ $data['page'] }}

**Nome:** {{ $data['name'] }}

**E-mail:** {{ $data['email'] }}

@if (isset($data['phone']) && !empty($data['phone']))
**Telefone:** {{ $data['phone'] }}
@endif

{{-- Property Fields --}}
@if (isset($data['role']) && !empty($data['role']))
**Tipo de negociação:** {{ $data['role'] }}
@endif

@if (isset($data['type']) && !empty($data['type']))
**Tipo do imóvel:** {{ $data['type'] }}
@endif

@if (isset($data['bedroom']) && !empty($data['bedroom']))
**Quarto(s):** {{ $data['bedroom'] }}
@endif

@if (isset($data['suite']) && !empty($data['suite']))
**Suíte(s):** {{ $data['suite'] }}
@endif

@if (isset($data['bathroom']) && !empty($data['bathroom']))
**Banheiro(s):** {{ $data['bathroom'] }}
@endif

@if (isset($data['garage']) && !empty($data['garage']))
**Vaga(s):** {{ $data['garage'] }}
@endif

@if (isset($data['useful_area']) && !empty($data['useful_area']))
**Área útil:** {{ $data['useful_area'] }}
@endif

@if (isset($data['total_area']) && !empty($data['total_area']))
**Área total:** {{ $data['total_area'] }}
@endif

@if (isset($data['sale_price']) && !empty($data['sale_price']))
**Preço da venda:** {{ $data['sale_price'] }}
@endif

@if (isset($data['rent_price']) && !empty($data['rent_price']))
**Preço do aluguel:** {{ $data['rent_price'] }}
@endif

@if (isset($data['tax_price']) && !empty($data['tax_price']))
**IPTU /ano:** {{ $data['tax_price'] }}
@endif

@if (isset($data['condo_price']) && !empty($data['condo_price']))
**Condomínio /mês:** {{ $data['condo_price'] }}
@endif
{{-- End::Property Fields --}}

{{-- Address Fields --}}
@if (isset($data['address']['zipcode']) && !empty($data['address']['zipcode']))
**CEP:** {{ $data['address']['zipcode'] }}
@endif

@if (isset($data['address']['uf']) && !empty($data['address']['uf']))
**Estado:** {{ $data['address']['uf'] }}
@endif

@if (isset($data['address']['city']) && !empty($data['address']['city']))
**Cidade:** {{ $data['address']['city'] }}
@endif

@if (isset($data['address']['district']) && !empty($data['address']['district']))
**Bairro:** {{ $data['address']['district'] }}
@endif

@if (isset($data['address']['address_line']) && !empty($data['address']['address_line']))
**Endereço:** {{ $data['address']['address_line'] }}
@endif

@if (isset($data['address']['number']) && !empty($data['address']['number']))
**Número:** {{ $data['address']['number'] }}
@endif

@if (isset($data['address']['complement']) && !empty($data['address']['complement']))
**Complemento:** {{ $data['address']['complement'] }}
@endif
{{-- End::Address Fields --}}

@if (isset($data['message']) && !empty($data['message']))
{{ $data['message'] }}
@endif
@endcomponent
