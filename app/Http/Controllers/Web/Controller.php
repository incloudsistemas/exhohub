<?php

namespace App\Http\Controllers\Web;

use App\Enums\RealEstate\EnterpriseRoleEnum;
use App\Models\Cms\Page;
use App\Models\RealEstate\PropertyType;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Jenssegers\Agent\Agent;

abstract class Controller
{
    protected array $mailTo = [
        'contato@exho.com.br'
    ];

    protected ?string $recaptchaSecret;

    public function __construct(protected Page $page)
    {
        $webSettings =  [
            'cnpj'  => '23.167.420/0001-62',
            'creci' => 'CRECI J 29.073',
            'mail' => [
                'contato@exho.com.br',
            ],
            'phones' => [
                [
                    'name'  => null,
                    'phone' => null,
                    'link'  => null,
                ],
            ],
            'whatsapp' => [
                [
                    'name'  => 'Exho Matriz',
                    'phone' => '(62) 99239-7060',
                    'link'  => 'https://wa.me/5562992397060?text=Ol%C3%A1%2C+vim+pelo+site.+Gostaria+de+saber+mais+detalhes...',
                ],
            ],
            'instagram' => [
                [
                    'name' => '@exhoimoveis',
                    'link' => 'https://www.instagram.com/exhoimoveis',
                ],
                [
                    'name' => '@exhoaluguel',
                    'link' => 'https://www.instagram.com/exhoaluguel',
                ],
            ],
            'addresses' => [
                [
                    'name'            => 'Exho Matriz',
                    'zipcode'         => '75110-780',
                    'state'           => 'Goiás',
                    'uf'              => 'GO',
                    'city'            => 'Anápolis',
                    'district'        => 'Jundiaí',
                    'address_line'    => 'R. Pedro Braz de Queirós, 191',
                    'coordinates'     => null,
                    'display_address' => 'R. Pedro Braz de Queirós, 191 - Jundiaí, Anápolis - GO, 75110-780',
                    'gmaps_link'      => 'https://maps.app.goo.gl/PnD3FhSGWZXAYnn7A',
                ],
            ],
        ];

        View::share('webSettings', $webSettings);

        $agent = new Agent();
        View::share('agent', $agent);

        $propertyTypes = Cache::rememberForever('property_types', function () {
            return [
                'residencial' => PropertyType::byUsages(usages: [1, 3])
                    ->byStatuses(statuses: $this->getPostStatusByUser())
                    ->pluck('name', 'id')
                    ->toArray(),
                'comercial'   => PropertyType::byUsages(usages: [2, 3])
                    ->byStatuses(statuses: $this->getPostStatusByUser())
                    ->pluck('name', 'id')
                    ->toArray(),
            ];
        });

        View::share('propertyTypes', $propertyTypes);

        $enterpriseRoles = EnterpriseRoleEnum::getAssociativeArray();
        View::share('enterpriseRoles', $enterpriseRoles);
    }

    protected function getPage(string $slug): ?Model
    {
        return $this->page->findWebBySlug(slug: $slug, statuses: $this->getPostStatusByUser())
            ->firstOrFail();
    }

    protected function getPostStatusByUser(): array
    {
        // Verify that the user is logged
        // If yes, show posts with status = 1 - Ativo and 2 - Rascunho
        if (auth()->guard('web')->check()) {
            return [1, 2];
        }

        // If not, show only posts with status 1 - Ativo
        return [1];
    }

    protected function generatePostSEOAttribute(Model $page, string $type = 'website'): void
    {
        $title = $page->cmsPost->meta_title ?? $page->cmsPost->title ?? $page->name ?? config('app.name', 'InCloud');
        SEOTools::setTitle(strip_tags($title));

        $description = $page->cmsPost->meta_description ?? $page->cmsPost->excerpt ?? $page->cmsPost->subtitle ?? $title;
        SEOTools::setDescription(strip_tags($description));

        SEOTools::opengraph()
            ->setUrl(URL::current());

        SEOTools::setCanonical(URL::current());

        SEOTools::opengraph()
            ->addProperty('type', $type);

        if (isset($this->twitter) && !empty($this->twitter)) {
            SEOTools::twitter()
                ->setSite($this->twitter);
        }

        $image = $page->featured_image
            ? CreateThumb(src: $page->featured_image->getUrl(), width: 300, height: 300)
            : asset('web-build/images/cover.jpg');

        SEOTools::opengraph()
            ->addImage($image);
    }
}
