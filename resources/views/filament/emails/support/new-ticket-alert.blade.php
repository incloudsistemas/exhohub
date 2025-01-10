@component('mail::message')
# Novo chamado **#{{ $data['id'] }}** cadastrado no sistema

O usuário **{{ $data['user'] }}** acabou de abrir um chamado no sistema e está aguardando resposta.

**Departamento:** {{ $data['department'] }}

!@empty($data['categories'])
**Categoria(s):** {{ implode(', ', $data['categories']) }}
@endempty

**Título:** {{ $data['title'] }}

@component('mail::button', ['url' => $data['action']])
Visualizar chamado
@endcomponent

@endcomponent
