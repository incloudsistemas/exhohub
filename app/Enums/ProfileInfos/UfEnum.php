<?php

namespace App\Enums\ProfileInfos;

use Filament\Support\Contracts\HasLabel;

enum UfEnum: string implements HasLabel
{
    case AC = 'AC';
    case AL = 'AL';
    case AP = 'AP';
    case AM = 'AM';
    case BA = 'BA';
    case CE = 'CE';
    case DF = 'DF';
    case ES = 'ES';
    case GO = 'GO';
    case MA = 'MA';
    case MT = 'MT';
    case MS = 'MS';
    case MG = 'MG';
    case PA = 'PA';
    case PB = 'PB';
    case PR = 'PR';
    case PE = 'PE';
    case PI = 'PI';
    case RJ = 'RJ';
    case RN = 'RN';
    case RS = 'RS';
    case RO = 'RO';
    case RR = 'RR';
    case SC = 'SC';
    case SP = 'SP';
    case SE = 'SE';
    case TO = 'TO';

    public function getLabel(): string
    {
        return match ($this) {
            self::AC => 'Acre',
            self::AL => 'Alagoas',
            self::AP => 'Amapá',
            self::AM => 'Amazonas',
            self::BA => 'Bahia',
            self::CE => 'Ceará',
            self::DF => 'Distrito Federal',
            self::ES => 'Espírito Santo',
            self::GO => 'Goiás',
            self::MA => 'Maranhão',
            self::MT => 'Mato Grosso',
            self::MS => 'Mato Grosso do Sul',
            self::MG => 'Minas Gerais',
            self::PA => 'Pará',
            self::PB => 'Paraíba',
            self::PR => 'Paraná',
            self::PE => 'Pernambuco',
            self::PI => 'Piauí',
            self::RJ => 'Rio de Janeiro',
            self::RN => 'Rio Grande do Norte',
            self::RS => 'Rio Grande do Sul',
            self::RO => 'Rondônia',
            self::RR => 'Roraima',
            self::SC => 'Santa Catarina',
            self::SP => 'São Paulo',
            self::SE => 'Sergipe',
            self::TO => 'Tocantins',
        };
    }
}
