<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the permissions tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'Superadministrador' => [
            // 'Permissões' => 'c,r,u,d',
        ],
        'Cliente' => [
            //
        ],
        'Administrador' => [
            'Níveis de Acessos' => 'c,r,u,d',
            'Usuários'          => 'c,r,u,d',
            'Agências'          => 'c,r,u,d',
            'Times'             => 'c,r,u,d',

            '[CRM] Tipos de Contatos'             => 'c,r,u,d',
            '[CRM] Origens dos Contatos/Negócios' => 'c,r,u,d',
            '[CRM] Contatos'                      => 'c,r,u,d',
            '[CRM] Funis de Negócios'             => 'c,r,u,d',
            '[CRM] Negócios'                      => 'c,r,u,d',
            '[CRM] Filas'                         => 'c,r,u,d',

            '[IMB] Tipos de Imóveis'             => 'c,r,u,d',
            '[IMB] Subtipos de Imóveis'          => 'c,r,u,d',
            '[IMB] Características dos Imóveis'  => 'c,r,u,d',
            '[IMB] Imóveis à Venda e/ou Aluguel' => 'c,r,u,d',
            '[IMB] Lançamentos'                  => 'c,r,u,d',

            '[CMS] Páginas'     => 'c,r,u,d',
            '[CMS] Blog'        => 'c,r,u,d',
            '[CMS] Depoimentos' => 'c,r,u,d',
            '[CMS] Parceiros'   => 'c,r,u,d',
            '[CMS] Categorias'  => 'c,r,u,d',
            '[CMS] Sliders'     => 'c,r,u,d',

            '[Suporte] Chamados'      => 'c,r,u,d',
            '[Suporte] Departamentos' => 'c,r,u,d',
            '[Suporte] Categorias'    => 'c,r,u,d',

            '[Financeiro] Contas Bancárias' => 'c,r,u,d',
            '[Financeiro] Contas a Pagar'   => 'c,r,u,d',
            '[Financeiro] Contas a Receber' => 'c,r,u,d',
            '[Financeiro] Transferências'   => 'c,r,u,d',
            '[Financeiro] Categorias'       => 'c,r,u,d',
        ],
        'Diretor' => [
            '[CRM] Contatos' => 'c,r,u,d',
            '[CRM] Negócios' => 'c,r,u,d',

            '[IMB] Imóveis à Venda e/ou Aluguel' => 'c,r,u,d',

            '[Suporte] Chamados' => 'c,r,u,d',
        ],
        'Gerente' => [
            '[CRM] Contatos' => 'c,r,u',
            '[CRM] Negócios' => 'c,r,u',

            '[IMB] Imóveis à Venda e/ou Aluguel' => 'c,r,u',

            '[Suporte] Chamados' => 'c,r,u,d',
        ],
        'Corretor' => [
            '[CRM] Contatos' => 'c,r,u',
            '[CRM] Negócios' => 'c,r,u',

            '[IMB] Imóveis à Venda e/ou Aluguel' => 'c,r,u',

            '[Suporte] Chamados' => 'c,r,u,d',
        ],
        'Captador' => [
            '[IMB] Imóveis à Venda e/ou Aluguel' => 'c,r,u',

            '[Suporte] Chamados' => 'c,r,u,d',
        ],
        'Suporte' => [
            '[Suporte] Chamados'      => 'c,r,u,d',
            // '[Suporte] Departamentos' => 'c,r,u,d',
            // '[Suporte] Categorias'    => 'c,r,u,d',
        ],
    ],

    'permissions_map' => [
        'c' => 'Cadastrar',
        'r' => 'Visualizar',
        'u' => 'Editar',
        'd' => 'Deletar'
    ]
];
