<?php

namespace App\Http\Controllers\Web;

use App\Models\Cms\BlogPost;
use App\Models\Cms\Page;
use App\Models\Cms\Partner;
use App\Models\Cms\Testimonial;
use App\Models\RealEstate\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DefaultPageController extends Controller
{
    public function __construct(
        protected Page $page,
        protected Property $property,
        protected Partner $partner,
        protected Testimonial $testimonial,
        protected BlogPost $blogPost,
    ) {
        parent::__construct(page: $page);
    }

    public function index(): \Illuminate\View\View
    {
        $page = Cache::rememberForever('index_page', function () {
            return $this->getPage(slug: 'index');
        });

        $this->generatePostSEOAttribute(page: $page);

        $subpages = Cache::rememberForever('index_subpages', function () {
            return [
                'to-sale'     => $this->getPage(slug: 'a-venda'),
                'to-rent'     => $this->getPage(slug: 'para-alugar'),
                'enterprises' => $this->getPage(slug: 'lancamentos'),
                'blog'        => $this->getPage(slug: 'blog'),
                'contact-us'  => $this->getPage(slug: 'fale-conosco')
            ];
        });

        $properties = Cache::rememberForever('index_properties', function () {
            return [
                'to-sale'     => $this->property->getWebFeaturedIndividualsByRoles(
                    roles: [1, 3],
                    statuses: $this->getPostStatusByUser()
                )
                    ->take(6)
                    ->get(),
                'to-rent'     => $this->property->getWebFeaturedIndividualsByRoles(
                    roles: [2, 3],
                    statuses: $this->getPostStatusByUser()
                )
                    ->take(6)
                    ->get(),
                'enterprises' => $this->property->getWebFeaturedEnterprisesByRoles(
                    roles: [1, 2, 3],
                    statuses: $this->getPostStatusByUser()
                )
                    ->take(6)
                    ->get(),
            ];
        });

        return view('web.pages.index', compact('page', 'subpages', 'properties'));
    }

    public function about(): \Illuminate\View\View
    {
        $page = Cache::rememberForever('about_page', function () {
            return $this->getPage(slug: 'quem-somos');
        });

        $this->generatePostSEOAttribute(page: $page);

        return view('web.pages.about', compact('page'));
    }

    public function contactUs(): \Illuminate\View\View
    {
        $page = Cache::rememberForever('contact_us_page', function () {
            return $this->getPage(slug: 'fale-conosco');
        });

        $this->generatePostSEOAttribute(page: $page);

        return view('web.pages.contact-us', compact('page'));
    }

    public function workWithUs(): \Illuminate\View\View
    {
        $page = Cache::rememberForever('work_with_us_page', function () {
            return $this->getPage(slug: 'trabalhe-conosco');
        });

        $this->generatePostSEOAttribute(page: $page);

        return view('web.pages.work-with-us', compact('page'));
    }

    public function linksTree(): \Illuminate\View\View
    {
        $page = Cache::rememberForever('links_tree_page', function () {
            return $this->getPage(slug: 'links');
        });

        $this->generatePostSEOAttribute(page: $page);

        return view('web.pages.links-tree', compact('page'));
    }

    public function rules($slug): \Illuminate\View\View
    {
        if (!in_array($slug, ['termos-de-uso', 'politica-de-privacidade'])) {
            abort(404);
        }

        $page = Cache::rememberForever(str_replace('-', '_', $slug) . '_page', function () use ($slug) {
            return $this->getPage(slug: $slug);
        });

        $this->generatePostSEOAttribute($page);

        return view('web.pages.rules', compact('page'));
    }
}
