@component('mail::message')
# Chamado **#{{ $data['id'] }} - {{ $data['title'] }}** foi encerrado.

### Detalhes do chamado:

!@empty($data['categories'])
**Categoria(s):** {{ implode(', ', $data['categories']) }}
@endempty

**Título:** {{ $data['title'] }}

**Departamento:** {{ $data['department'] }}

**Data de criação:** {{ $data['created_at'] }}

**Data de abertura:** {{ $data['opened_at'] }}

**Data de encerramento:** {{ $data['closed_at'] }}

@component('mail::button', ['url' => $data['action']])
Visualizar chamado
@endcomponent

@endcomponent
