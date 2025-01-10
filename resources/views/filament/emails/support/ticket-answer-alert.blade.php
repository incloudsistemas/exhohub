@component('mail::message')
# Novo comentário no chamado #{{ $data['id'] }}

O chamado **{{ $data['title'] }}** recebeu uma nova resposta de **{{ $data['user'] }}**.

@component('mail::button', ['url' => $data['action']])
Visualizar chamado
@endcomponent

@endcomponent
