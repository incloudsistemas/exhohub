@component('mail::message')
# Novo usuário cadastrado no sistema

Um novo usuário acabou de se cadastrar no sistema e está aguardando aprovação.

**Nome:** {{ $data['name'] }}

**E-mail:** {{ $data['email'] }}

**Agência:** {{ $data['agency_name'] }}

**Time:** {{ $data['team_name'] }}

@component('mail::button', ['url' => $data['action']])
Visualizar usuário
@endcomponent

@endcomponent
