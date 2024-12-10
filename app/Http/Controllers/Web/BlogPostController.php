<?php

namespace App\Http\Controllers\Web;

use App\Enums\Cms\BlogPostRoleEnum;
use App\Models\Cms\BlogPost;
use App\Models\Cms\Page;
use App\Models\Cms\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

class BlogPostController extends Controller
{
    protected string $idxPage = 'blog';
    protected int $paginateNum = 9;

    public function __construct(
        protected Page $page,
        protected BlogPost $blogPost,
        protected PostCategory $category
    ) {
        parent::__construct($page);

        Paginator::useBootstrap();

        $modelType = MorphMapByClass(model: get_class($this->blogPost));
        $blogCategories = Cache::rememberForever('blog_categories', function () use ($modelType) {
            return $this->category->getWebPostCategoriesByTypes(postableTypes: [$modelType,])
                ->get();
        });

        View::share('blogCategories', $blogCategories);
    }

    public function index(): \Illuminate\View\View
    {
        $page = Cache::rememberForever('page_' . $this->idxPage . '_index', function () {
            return $this->getPage(slug: $this->idxPage);
        });

        $this->generatePostSEOAttribute(page: $page);

        $blogPosts = $this->blogPost->getWeb(statuses: $this->getPostStatusByUser())
            ->paginate($this->paginateNum);

        return view('web.blog.index', compact('page', 'blogPosts'));
    }

    public function show(string $slug): \Illuminate\View\View
    {
        $page = $this->blogPost->findWebBySlug(slug: $slug, statuses: $this->getPostStatusByUser())
            ->firstOrFail();

        $this->generatePostSEOAttribute(page: $page);

        $idxPage = $this->getPage(slug: $this->idxPage);

        $galleryItems = isset($page->gallery_items)
            ? $page->gallery_items
            : null;

        // Get related posts by categories...
        $categoryIds = $page->postCategories->pluck('id')
            ->toArray();

        $relatedPosts = $this->blogPost->getWebByRelatedCategories(
            categoryIds: $categoryIds,
            statuses: $this->getPostStatusByUser(),
            idToAvoid: $page->id
        )
            ->get();

        return view('web.blog.show', compact('page', 'idxPage', 'galleryItems', 'relatedPosts'));
    }

    public function indexByCategory(string $category): \Illuminate\View\View
    {
        $page = Cache::rememberForever('page_' . $this->idxPage . '_index', function () {
            return $this->getPage(slug: $this->idxPage);
        });

        $this->generatePostSEOAttribute(page: $page);

        $blogPosts = $this->blogPost->getWebByCategory(categorySlug: $category, statuses: $this->getPostStatusByUser())
            ->paginate($this->paginateNum);

        return view('web.blog.index', compact('page', 'category', 'blogPosts'));
    }

    public function indexByRole(string $role): \Illuminate\View\View
    {
        $page = Cache::rememberForever('page_' . $this->idxPage . '_index', function () {
            return $this->getPage(slug: $this->idxPage);
        });

        $this->generatePostSEOAttribute(page: $page);

        $blogRoles = BlogPostRoleEnum::getAssociativeArray();

        $blogRoleValue = BlogPostRoleEnum::getValueFromSlug(slug: $role);

        $displayRole = BlogPostRoleEnum::getLabelFromSlug(slug: $role);

        $blogPosts = $this->blogPost->getWebByRoles(roles: [$blogRoleValue], statuses: $this->getPostStatusByUser())
            ->paginate($this->paginateNum);

        return view('web.blog.index', compact('page', 'role', 'blogRoles', 'displayRole', 'blogPosts'));
    }

    // public function search(Request $request): \Illuminate\View\View
    // {
    //     $page = Cache::rememberForever('page_' . $this->idxPage . '_index', function () {
    //         return $this->getPage(slug: $this->idxPage);
    //     });

    //     $this->generatePostSEOAttribute(page: $page);

    //     $data = $request->all();

    //     $blogPosts = $this->blogPost->searchWeb(keyword: $data['keyword'], statuses: $this->getPostStatusByUser())
    //         ->paginate($this->paginateNum);

    //     return view("web.{$this->i2cProjectPath}.blog.index", compact('page', 'data', 'blogPosts'));
    // }

    // public function loadPosts(Request $request)
    // {
    //     $request->validate([
    //         'category' => 'string|nullable',
    //         'role'     => 'string|nullable',
    //         'search'   => 'string|nullable',
    //     ]);

    //     $data = $request->all();

    //     if (isset($data['category'])) {
    //         $blogPosts = $this->blogPost->getWebByCategory(categorySlug: $data['category'], statuses: $this->getPostStatusByUser())
    //             ->paginate($this->paginateNum);
    //     } elseif (isset($data['role'])) {
    //         $slugs = BlogRole::getSlug();
    //         $roles = [array_search($data['role'], $slugs)];

    //         $blogPosts = $this->blogPost->getWebByRoles(roles: $roles, statuses: $this->getPostStatusByUser())
    //             ->paginate($this->paginateNum);
    //     } elseif (isset($data['search'])) {
    //         $blogPosts = $this->blogPost->searchWeb(keyword: $data['search'], statuses: $this->getPostStatusByUser())
    //             ->paginate($this->paginateNum);
    //     } else {
    //         $blogPosts = $this->blogPost->getWeb(statuses: $this->getPostStatusByUser())
    //             ->paginate($this->paginateNum);
    //     }

    //     $lastPage = ($blogPosts->currentPage() == $blogPosts->lastPage());

    //     return view("web.{$this->i2cProjectPath}.blog._partials._posts", compact('data', 'blogPosts', 'lastPage'));
    // }
}
