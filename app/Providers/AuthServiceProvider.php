<?php

namespace App\Providers;

use App\Models;
use App\Policies;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Models\System\Permission::class => Policies\System\PermissionPolicy::class,
        Models\System\Role::class       => Policies\System\RolePolicy::class,
        Models\System\User::class       => Policies\System\UserPolicy::class,
        Models\System\Agency::class     => Policies\System\AgencyPolicy::class,
        Models\System\Team::class       => Policies\System\TeamPolicy::class,

        Models\Crm\Source::class               => Policies\Crm\SourcePolicy::class,
        Models\Crm\Contacts\Role::class        => Policies\Crm\Contacts\RolePolicy::class,
        Models\Crm\Contacts\Individual::class  => Policies\Crm\Contacts\IndividualPolicy::class,
        Models\Crm\Contacts\LegalEntity::class => Policies\Crm\Contacts\LegalEntityPolicy::class,
        Models\Crm\Funnels\Funnel::class       => Policies\Crm\Funnels\FunnelPolicy::class,
        Models\Crm\Business\Business::class    => Policies\Crm\Business\BusinessPolicy::class,
        Models\Crm\Queues\Queue::class         => Policies\Crm\Queues\QueuePolicy::class,

        Models\RealEstate\PropertyType::class           => Policies\RealEstate\PropertyTypePolicy::class,
        Models\RealEstate\PropertySubtype::class        => Policies\RealEstate\PropertySubtypePolicy::class,
        Models\RealEstate\PropertyCharacteristic::class => Policies\RealEstate\PropertyCharacteristicPolicy::class,
        Models\RealEstate\Enterprise::class             => Policies\RealEstate\EnterprisePolicy::class,
        Models\RealEstate\Individual::class             => Policies\RealEstate\IndividualPolicy::class,

        Models\Cms\Page::class         => Policies\Cms\PagePolicy::class,
        Models\Cms\BlogPost::class     => Policies\Cms\BlogPostPolicy::class,
        Models\Cms\Testimonial::class  => Policies\Cms\TestimonialPolicy::class,
        Models\Cms\Partner::class      => Policies\Cms\PartnerPolicy::class,
        Models\Cms\PostCategory::class => Policies\Cms\PostCategoryPolicy::class,
        Models\Cms\PostSlider::class   => Policies\Cms\MainPostSliderPolicy::class,

        Models\Support\Ticket::class         => Policies\Support\TicketPolicy::class,
        Models\Support\Department::class     => Policies\Support\DepartmentPolicy::class,
        Models\Support\TicketCategory::class => Policies\Support\TicketCategoryPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Implicitly grant "Superadmin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Superadministrador') ? true : null;
        });

        $this->registerPolicies();
    }
}
