<?php

namespace App\Http\Controllers\Web\RealEstate;

use App\Enums\RealEstate\EnterpriseRoleEnum;
use App\Http\Controllers\Web\Controller;
use App\Models\Cms\Page;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertyType;
use App\Services\Web\RealEstate\PropertyService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Artesaos\SEOTools\Facades\SEOTools;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PropertyController extends Controller
{
    protected int $paginateNum = 9;

    public function __construct(
        protected Page $page,
        protected Property $property,
        protected PropertyService $service,
    ) {
        parent::__construct(page: $page);

        Paginator::useBootstrap();

        $this->recaptchaSecret = config('app.g_recapcha_server');
    }

    public function show(string $slug, string $code): \Illuminate\View\View
    {
        $page = $this->property->findWebBySlug(slug: $slug, statuses: $this->getPostStatusByUser())
            ->firstOrFail();

        $this->generatePropertySEOAttribute(page: $page);

        $idxPage = $this->getIdxPageByProperty(property: $page);

        $galleryImages = $page->propertable->gallery_images;
        $galleryVideos = $page->propertable->gallery_videos;

        $characteristics['differences'] = $page->differences_characteristics;
        $characteristics['leisure'] = $page->leisure_characteristics;
        $characteristics['security'] = $page->security_characteristics;
        $characteristics['infrastructure'] = $page->infrastructure_characteristics;

        $relatedProperties = $this->getRelatedProperties(property: $page);

        return view('web.properties.show', compact(
            'page',
            'idxPage',
            'galleryImages',
            'galleryVideos',
            'characteristics',
            'relatedProperties'
        ));
    }

    public function search(Request $request)
    {
        $data = $request->all();

        if (!isset($data['role'])) {
            abort(404);
        }

        $page = Cache::rememberForever('page_' . str_replace('-', '_', $data['role']) . '_index', function () use ($data) {
            return $this->getPage(slug: $data['role']);
        });

        $this->generatePostSEOAttribute(page: $page);

        $propertyTypes = $this->getPropertyTypes();

        $enterpriseRoles = EnterpriseRoleEnum::getAssociativeArray();

        $properties = $this->getSearchPropertiesByPageRole(data: $data)
            ->whereJsonContains('publish_on->portal_web', true)
            ->paginate($this->paginateNum);

        $role = $data['role'] === 'lancamentos' ? null : $data['role'];

        if (isset($data['_token']) && $properties->count() > 0) {
            $response = $this->service->createSearch($data);
            $search = $response['success'] ? $response['data'] : null;

            // If errors...
            if (!$search) {
                return redirect()
                    ->back()
                    ->withErrors($response['message'])
                    ->withInput();
            }
        }

        return view('web.properties.index', compact(
            'page',
            'data',
            'role',
            'propertyTypes',
            'enterpriseRoles',
            'properties'
        ));
    }

    protected function getIdxPageByProperty(Property $property): ?Page
    {
        if ($property->propertable_type === 'real_estate_enterprises') {
            return $this->getPage(slug: 'lancamentos');
        }

        if ((int) $property->propertable->role->value === 1) {
            return $this->getPage(slug: 'a-venda');
        }

        if ((int) $property->propertable->role->value === 2) {
            return $this->getPage(slug: 'para-alugar');
        }

        return null;
    }

    protected function getSearchPropertiesByPageRole(array $data): Builder
    {
        $roleMappings = [
            'lancamentos' => [1, 2, 3],
            'a-venda'     => [1, 3],
            'para-alugar' => [2, 3],
        ];

        if (!array_key_exists($data['role'], $roleMappings)) {
            abort(404);
        }

        $arrRoles = $roleMappings[$data['role']];

        if ($data['role'] === 'lancamentos') {
            return $this->service->searchWebEnterprises(
                data: $data,
                roles: $arrRoles,
                statuses: $this->getPostStatusByUser()
            );
        }

        return $this->service->searchWebIndividuals(
            data: $data,
            roles: $arrRoles,
            statuses: $this->getPostStatusByUser()
        );
    }

    protected function getRelatedProperties(Property $property): Collection
    {
        if ($property->propertable_type === 'real_estate_individuals') {
            $roles = $property->propertable->role === 3 ? [1, 2, 3] : [$property->propertable->role, 3];

            return $this->property->getWebIndividualsByRelatedUsageType(
                roles: $roles,
                usage: $property->usage->value,
                type: $property->type_id,
                statuses: $this->getPostStatusByUser(),
                idToAvoid: $property->id
            )
                ->take(6)
                ->get();
        }

        return $this->property->getWebEnterprisesByRelatedUsageType(
            roles: [$property->propertable->role],
            usage: $property->usage->value,
            type: $property->type_id,
            statuses: $this->getPostStatusByUser(),
            idToAvoid: $property->id
        )
            ->take(6)
            ->get();
    }

    protected function getPropertyTypes(): array
    {
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

        return $propertyTypes;
    }

    protected function generatePropertySEOAttribute(Property $page): void
    {
        $title = $page->meta_title ?? $page->title ?? $page->name ?? config('app.name', 'InCloud');
        SEOTools::setTitle(strip_tags($title));

        $description = $page->meta_description ?? $page->excerpt ?? $page->subtitle ?? $title;
        SEOTools::setDescription(strip_tags($description));

        SEOTools::opengraph()
            ->setUrl(Url::current());

        SEOTools::setCanonical(URL::current());

        SEOTools::opengraph()
            ->addProperty('type', 'website');

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
