<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // // Create storage folder
        // // utilizar apenas no ambiente de produção em host compartilhado.
        // if (!file_exists('storage')) {
        //     \App::make('files')->link(storage_path('app/public'), 'storage');
        // }

        // // Public Path
        // // utilizar apenas no ambiente de produção em host compartilhado.
        // app()->usePublicPath(realpath(base_path() . '/..'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Morph map for polymorphic relations.
        $this->registerMorphMaps();
    }

    protected function registerMorphMaps(): void
    {
        Relation::morphMap([
            'permissions' => 'App\Models\System\Permission',
            'roles'       => 'App\Models\System\Role',
            'users'       => 'App\Models\System\User',
            'agencies'    => 'App\Models\System\Agency',
            'teams'       => 'App\Models\System\Team',

            'creci_control_stages' => 'App\Models\System\CreciControlStage',
            'user_creci_stages'    => 'App\Models\System\UserCreciStage',

            'crm_sources'                => 'App\Models\Crm\Source',
            'crm_contact_roles'          => 'App\Models\Crm\Contacts\Role',
            'crm_contacts'               => 'App\Models\Crm\Contacts\Contact',
            'crm_contact_individuals'    => 'App\Models\Crm\Contacts\Individual',
            'crm_contact_legal_entities' => 'App\Models\Crm\Contacts\LegalEntity',

            'crm_funnels'                               => 'App\Models\Crm\Funnels\Funnel',
            'crm_funnel_stages'                         => 'App\Models\Crm\Funnels\FunnelStage',
            'crm_funnel_substages'                      => 'App\Models\Crm\Funnels\FunnelSubstage',
            'crm_business'                              => 'App\Models\Crm\Business\Business',
            'crm_business_funnel_stages'                => 'App\Models\Crm\Business\BusinessFunnelStage',
            'crm_business_properties_interest_profiles' => 'App\Models\Crm\Business\PropertiesInterestProfile',
            'crm_queues'                                => 'App\Models\Crm\Queues\Queue',

            'activities'               => 'App\Models\Activities\Activity',
            'activity_notifications'   => 'App\Models\Activities\Notification',
            'activity_web_conversions' => 'App\Models\Activities\WebConversion',

            'real_estate_property_types'           => 'App\Models\RealEstate\PropertyType',
            'real_estate_property_subtypes'        => 'App\Models\RealEstate\PropertySubtype',
            'real_estate_property_characteristics' => 'App\Models\RealEstate\PropertyCharacteristic',
            'real_estate_properties'               => 'App\Models\RealEstate\Property',
            'real_estate_individuals'              => 'App\Models\RealEstate\Individual',
            'real_estate_enterprises'              => 'App\Models\RealEstate\Enterprise',
            'real_estate_property_searches'        => 'App\Models\RealEstate\PropertySearch',

            'cms_posts'           => 'App\Models\Cms\Post',
            'cms_pages'           => 'App\Models\Cms\Page',
            'cms_blog_posts'      => 'App\Models\Cms\BlogPost',
            'cms_testimonials'    => 'App\Models\Cms\Testimonial',
            'cms_partners'        => 'App\Models\Cms\Partner',
            'cms_post_categories' => 'App\Models\Cms\PostCategory',
            'cms_post_sliders'    => 'App\Models\Cms\PostSlider',

            'support_departments' => 'App\Models\Support\Department',
            'tickets'             => 'App\Models\Support\Ticket',
            'ticket_categories'   => 'App\Models\Support\TicketCategory',
            'ticket_comments'     => 'App\Models\Support\TicketComment',

            'financial_bank_institutions' => 'App\Models\Financial\BankInstitution',
            'financial_bank_accounts'     => 'App\Models\Financial\BankAccount',
            'financial_transactions'      => 'App\Models\Financial\Transaction',
            'financial_categories'        => 'App\Models\Financial\Category',

            'addresses' => 'App\Models\Polymorphics\Address',
        ]);
    }
}
