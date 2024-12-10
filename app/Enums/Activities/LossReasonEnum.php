<?php

namespace App\Enums\Activities;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum LossReasonEnum: string implements HasLabel
{
    use EnumHelper;

    case NEGOTIATED_WITH_OTHERS      = '1';
    case DISLIKED_SERVICE            = '2';
    case OWNER_WITHDREW              = '3';
    case PRICE_TOO_HIGH              = '4';
    case LOST_CONTACT                = '5';
    case CLIENT_RESEARCHING          = '6';
    case LOAN_NOT_APPROVED           = '7';
    case CONTACT_LATER               = '8';
    case INVALID_CONTACT_INFORMATION = '9';
    case NO_CONTACT                  = '10';
    case CONTRACT_DISAGREEMENT       = '11';
    case WITHDREW_FROM_DEAL          = '12';
    case OTHER                       = '13';

    public function getLabel(): string
    {
        return match ($this) {
            self::NEGOTIATED_WITH_OTHERS      => 'Negociou com outra pessoa/empresa',
            self::DISLIKED_SERVICE            => 'Não gostou do atendimento',
            self::OWNER_WITHDREW              => 'Proprietário desistiu do negócio',
            self::PRICE_TOO_HIGH              => 'Valores acima do esperado',
            self::LOST_CONTACT                => 'Perdido contato com o cliente',
            self::CLIENT_RESEARCHING          => 'Cliente pesquisando',
            self::LOAN_NOT_APPROVED           => 'Financiamento não aprovado',
            self::CONTACT_LATER               => 'Contatar futuramente',
            self::INVALID_CONTACT_INFORMATION => 'Dados do contato inválido',
            self::NO_CONTACT                  => 'Sem contato',
            self::CONTRACT_DISAGREEMENT       => 'Desacordo de contrato',
            self::WITHDREW_FROM_DEAL          => 'Desistiu do negócio',
            self::OTHER                       => 'Outro',
        };
    }
}
