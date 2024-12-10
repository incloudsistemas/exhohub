<?php

namespace App\Http\Controllers\Web\RealEstate;

use App\Enums\RealEstate\EnterpriseRoleEnum;
use App\Enums\RealEstate\PropertyUsageEnum;
use App\Models\RealEstate\PropertyType;
use Illuminate\Support\Facades\Cache;

class EnterpriseController extends PropertyController
{
    protected string $idxPage = 'lancamentos';

    public function index(): \Illuminate\View\View
    {
        $page = Cache::rememberForever('page_' . $this->idxPage . '_index', function () {
            return $this->getPage(slug: $this->idxPage);
        });

        $this->generatePostSEOAttribute(page: $page);

        $propertyTypes = $this->getPropertyTypes();

        $enterpriseRoles = EnterpriseRoleEnum::getAssociativeArray();

        $properties = $this->property->getWebEnterprisesByRoles(
            roles: array_keys($enterpriseRoles)
        )
            ->where('publish_on->portal_web', true)
            ->paginate($this->paginateNum);

        return view('web.properties.index', compact('page', 'propertyTypes', 'enterpriseRoles', 'properties'));
    }

    public function indexByRole(string $role): \Illuminate\View\View
    {
        $page = Cache::rememberForever('page_' . $this->idxPage . '_index', function () {
            return $this->getPage(slug: $this->idxPage);
        });

        $this->generatePostSEOAttribute(page: $page);

        $propertyTypes = $this->getPropertyTypes();

        $enterpriseRoles = EnterpriseRoleEnum::getAssociativeArray();

        $enterpriseRoleValue = EnterpriseRoleEnum::getValueFromSlug(slug: $role);

        $displayRole = EnterpriseRoleEnum::getLabelFromSlug(slug: $role);

        $properties = $this->property->getWebEnterprisesByRoles(
            roles: [$enterpriseRoleValue]
        )
            ->where('publish_on->portal_web', true)
            ->paginate($this->paginateNum);

        return view('web.properties.index', compact(
            'page',
            'role',
            'propertyTypes',
            'enterpriseRoles',
            'displayRole',
            'properties'
        ));
    }

    public function indexByUsageType(string $usage, string $type): \Illuminate\View\View
    {
        $page = Cache::rememberForever('page_' . $this->idxPage . '_index', function () {
            return $this->getPage(slug: $this->idxPage);
        });

        $this->generatePostSEOAttribute(page: $page);

        $propertyUsageValue = PropertyUsageEnum::getValueFromSlug(slug: $usage);

        $type = PropertyType::where('slug', $type)
            ->firstOrFail();

        $propertyTypes = $this->getPropertyTypes();

        $enterpriseRoles = EnterpriseRoleEnum::getAssociativeArray();

        $properties = $this->property->getWebEnterprisesByRelatedUsageType(
            roles: array_keys($enterpriseRoles),
            usage: $propertyUsageValue,
            type: $type->id
        )
            ->where('publish_on->portal_web', true)
            ->paginate($this->paginateNum);

        return view('web.properties.index', compact(
            'page',
            'usage',
            'type',
            'propertyTypes',
            'enterpriseRoles',
            'properties'
        ));
    }
}
