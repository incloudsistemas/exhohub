<?php

namespace App\Http\Controllers\Web\RealEstate;

use App\Enums\RealEstate\PropertyUsageEnum;
use App\Models\RealEstate\PropertyType;
use Illuminate\Support\Facades\Cache;

class IndividualController extends PropertyController
{
    public function index(string $role): \Illuminate\View\View
    {
        $page = Cache::rememberForever('page_'. str_replace('-', '_', $role) .'_index', function () use ($role) {
            return $this->getPage(slug: $role);
        });

        $this->generatePostSEOAttribute(page: $page);

        $propertyTypes = $this->getPropertyTypes();

        $individualRoles = $this->getIndividualRoles(role: $role);

        $properties = $this->property->getWebIndividualsByRoles(
            roles: $individualRoles
        )
            ->whereJsonContains('publish_on->portal_web', true)
            ->paginate($this->paginateNum);

        return view('web.properties.index', compact('page', 'role', 'propertyTypes', 'properties'));
    }

    public function indexByUsageType(string $role, string $usage, string $type): \Illuminate\View\View
    {
        $page = Cache::rememberForever('page_'. str_replace('-', '_', $role) .'_index', function () use ($role) {
            return $this->getPage(slug: $role);
        });

        $this->generatePostSEOAttribute(page: $page);

        $propertyUsageValue = PropertyUsageEnum::getValueFromSlug(slug: $usage);

        $type = PropertyType::where('slug', $type)
            ->firstOrFail();

        $propertyTypes = $this->getPropertyTypes();

        $individualRoles = $this->getIndividualRoles(role: $role);

        $properties = $this->property->getWebIndividualsByRelatedUsageType(
            roles: $individualRoles,
            usage: $propertyUsageValue,
            type: $type->id
        )
            ->whereJsonContains('publish_on->portal_web', true)
            ->paginate($this->paginateNum);

        return view('web.properties.index', compact(
            'page',
            'role',
            'usage',
            'type',
            'propertyTypes',
            'properties'
        ));
    }

    protected function getIndividualRoles(string $role): array
    {
        return match ($role) {
            'a-venda'     => [1, 3],
            'para-alugar' => [2, 3],
            default       => abort(404),
        };
    }
}
