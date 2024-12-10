@component('mail::message')
# Conta criada com sucesso!

Olá, {{ $data['name'] }},

Estamos felizes em informar que sua conta foi criada com sucesso! Agora você pode acessar o sistema e aproveitar todos os recursos disponíveis.

@component('mail::button', ['url' => $data['action']])
Acessar sistema
@endcomponent

Se você tiver alguma dúvida ou precisar de ajuda, nossa equipe de suporte está sempre disponível para ajudá-lo.

Obrigado por se juntar a nós!

@endcomponent
