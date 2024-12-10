<?php

namespace App\Enums\Cms;

use App\Traits\EnumHelper;
use Filament\Support\Contracts\HasLabel;

enum BlogPostRoleEnum: string implements HasLabel
{
    use EnumHelper;

    case ARTICLE = '1';
    case LINK    = '2';
    case GALLERY = '3';
    case VIDEO   = '4';

    public function getLabel(): string
    {
        return match ($this) {
            self::ARTICLE => 'Artigo',
            self::LINK    => 'Link',
            self::GALLERY => 'Galeria de Fotos e Vídeos',
            self::VIDEO   => 'Vídeo',
        };
    }

    public function getSlug(): string
    {
        return match ($this) {
            self::ARTICLE => 'artigo',
            self::LINK    => 'link',
            self::GALLERY => 'galeria',
            self::VIDEO   => 'video',
        };
    }
}
