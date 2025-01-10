@component('mail::message')
# Cadastro aprovado!

Olá, {{ $data['name'] }},

Estamos felizes em informar que seu cadastro foi aprovado! Agora você pode acessar o sistema e começar a utilizar todos os nossos recursos.

@component('mail::button', ['url' => $data['action']])
Acessar sistema
@endcomponent

Se você tiver alguma dúvida ou precisar de ajuda, nossa equipe de suporte está sempre disponível para ajudá-lo.

Obrigado por se juntar a nós!

@endcomponent
