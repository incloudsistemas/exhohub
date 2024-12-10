<?php

namespace App\Services\Web;

use App\Enums\Crm\Queues\QueueRoleEnum;
use App\Mail\Web\BusinessLeadFormAlert;
use App\Models\Activities\Notification;
use App\Models\Crm\Business\Business;
use App\Models\Crm\Contacts\Individual;
use App\Models\Crm\Funnels\Funnel;
use App\Models\Crm\Queues\Queue;
use App\Models\Crm\Source;
use App\Models\RealEstate\Individual as PropertyIndividual;
use App\Models\RealEstate\Enterprise as PropertyEnterprise;
use App\Models\RealEstate\Property;
use App\Models\System\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BusinessLeadService extends BaseFormService
{
    protected ?Property $propertyData;
    protected ?Queue $queueData;
    protected User $ownerData;
    protected User $superadmin;
    protected Individual $individualData;
    protected Business $businessData;
    protected ?string $leadIp;

    public function __construct(
        protected Property $property,
        protected Queue $queue,
        protected User $user,
        protected Individual $individual,
        protected Source $source,
        protected Business $business,
        protected Funnel $funnel,
    ) {
        //
    }

    public function createFromWeb(array $data, array $mailTo, ?string $recaptchaSecret = null)
    {
        DB::beginTransaction();

        try {
            $this->honeyPotCheckBot($data);
            $this->reCaptchaProtection($data, $recaptchaSecret);

            $customMessages = $data['custom_messages'] ?? array();
            $this->setCustomMessages($customMessages);

            $this->leadIp = request()->ip();

            $queueRole = 'website';
            $data = $this->mutateFormDataBeforeCreate(data: $data, queueRole: $queueRole);

            $this->superadmin = $this->getSuperAdminUser();

            $this->propertyData = $this->getPropertyByCode(code: $data['property_code']);

            $this->queueData = $this->getQueueByPropertySettings(queueRole: $data['queue_role']);

            $this->ownerData = $this->getOwnerByQueueDistributionSettings();

            $this->individualData = $this->createLead(data: $data);
            $data['contact_id'] = $this->individualData->contact->id;

            $this->businessData = $this->createBusiness(data: $data);

            // Sync property to business
            $this->syncPropertyToBusiness(data: $data);

            $this->sendEmailNotification(data: $data, mailTo: $mailTo);

            DB::commit();

            return [
                'success'   => true,
                'from'      => 'web',
                'message'   => $this->message['success'],
                'data'      => $this->businessData,
                'fbq_track' => $data['fbq_track'] ?? null,
            ];
        } catch (\Exception $e) {
            DB::rollback();

            return $this->getErrorException($e);
        }
    }

    public function createFromCanalPro(array $data, array $mailTo)
    {
        DB::beginTransaction();

        try {
            $queueRole = 'grupo-olx-canalpro';
            $data = $this->mutateFormDataBeforeCreate(data: $data, queueRole: $queueRole);

            $this->superadmin = $this->getSuperAdminUser();

            $this->propertyData = $this->getPropertyByCode(code: $data['property_code']);

            if (!$this->propertyData) {
                // $this->handlePropertyNotFound();
                // exit;
            }

            $this->queueData = $this->getQueueByPropertySettings(queueRole: $queueRole);

            $this->ownerData = $this->getOwnerByQueueDistributionSettings();

            $this->individualData = $this->createLead(data: $data);
            $data['contact_id'] = $this->individualData->contact->id;

            $this->businessData = $this->createBusiness(data: $data);

            // Sync property to business
            $this->syncPropertyToBusiness(data: $data);

            $this->sendEmailNotification(data: $data, mailTo: $mailTo);

            DB::commit();

            return [
                'success'   => true,
                'from'      => 'web',
                'message'   => $this->message['success'],
                'data'      => $this->businessData,
                'fbq_track' => $data['fbq_track'] ?? null,
            ];
        } catch (\Exception $e) {
            DB::rollback();

            return $this->getErrorException($e);
        }
    }

    public function createFromMetaAds(array $data, array $mailTo)
    {
        DB::beginTransaction();

        try {
            $queueRole = 'meta-ads';
            $data = $this->mutateFormDataBeforeCreate(data: $data, queueRole: $queueRole);

            $this->superadmin = $this->getSuperAdminUser();

            $this->queueData = $this->getQueueByAccountAndCampaignId(data: $data);

            $this->propertyData = $this->getPropertyByQueue();

            $this->ownerData = $this->getOwnerByQueueDistributionSettings();

            $this->individualData = $this->createLead(data: $data);
            $data['contact_id'] = $this->individualData->contact->id;

            $this->businessData = $this->createBusiness(data: $data);

            // Sync property to business
            $this->syncPropertyToBusiness(data: $data);

            $this->sendEmailNotification(data: $data, mailTo: $mailTo);

            DB::commit();

            return [
                'success'   => true,
                'from'      => 'web',
                'message'   => $this->message['success'],
                'data'      => $this->businessData,
                'fbq_track' => $data['fbq_track'] ?? null,
            ];
        } catch (\Exception $e) {
            DB::rollback();

            return $this->getErrorException($e);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data, string $queueRole): array
    {
        // From CanalPro
        if ($queueRole === 'grupo-olx-canalpro') {

            $data['property_code'] = $data['clientListingId'];

            $fullNumber = $data['ddd'] . $data['phone'];

            if (strlen($data['phone']) === 9) { // (11) 99999-9999
                return sprintf("(%s) %s-%s", $data['ddd'], substr($data['phone'], 0, 5), substr($data['phone'], 5));
            } elseif (strlen($data['phone']) === 8) { // (11) 9999-9999
                return sprintf("(%s) %s-%s", $data['ddd'], substr($data['phone'], 0, 4), substr($data['phone'], 4));
            } else {
                return $fullNumber;
            }
        }

        if (isset($data['phone'])) {
            $data['phones'] = [[
                'name'   => null,
                'number' => $data['phone']
            ]];
        }

        $data['queue_role'] = QueueRoleEnum::getValueFromSlug(slug: $queueRole);

        $sourceId = match ($queueRole) {
            'website'            => 1, // 1 - Website
            'grupo-olx-canalpro' => 2, // 2 - Grupo OLX (CanalPro)
            'meta-ads'           => 3, // 3 - Meta Ads
            default              => null,
        };

        $source = $this->source->where('id', $sourceId)
            ->where('status', 1) // 1 - Ativo/Active
            ->first();

        $data['source_id'] = $source?->id ?? null;

        $data['business_at'] = now();

        return $data;
    }

    protected function getSuperAdminUser(): User
    {
        return $this->user->whereHas('roles', function (Builder $query): Builder {
            return $query->where('id', 1); // 1 - Superadmin
        })
            ->where('status', 1) // 1 - Ativo
            ->orderBy('created_at', 'asc')
            ->firstOrFail();
    }

    protected function getPropertyByCode(string $code): ?Property
    {
        return $this->property->with('owner:id,name,email')
            ->where('code', $code)
            ->first();
    }

    protected function getQueueByPropertySettings(string $queueRole): ?Queue
    {
        // Get the highest order available among all active records by role
        $currentOrder = $this->queue->where('role', $queueRole)
            ->where('status', 1) // 1 - Ativo
            ->max('order');

        // Loop to keep searching lower orders if no match is found
        while ($currentOrder !== null) {
            // Base query with the current order
            $baseQuery = $this->queue->where('role', $queueRole)
                ->where('status', 1) // 1 - Ativo
                ->where('order', $currentOrder)
                ->orderBy('created_at', 'desc');

            // 1. First attempt with a property-specific filter
            $query = clone $baseQuery;
            $queueData = $query->whereHas('properties', function (Builder $query): Builder {
                return $query->where('property_id', $this->propertyData->id);
            })
                ->first();

            if (isset($queueData)) {
                return $queueData;
            }

            // 2. Any property or enterprise
            $query = clone $baseQuery;
            $queueData = $query->where('properties_settings', 2) // 2 - Todos os imóveis e empreendimentos
                ->first();

            if (isset($queueData)) {
                return $queueData;
            }

            // 3. Check if the property type is individual and try to find
            $individualType = MorphMapByClass(model: PropertyIndividual::class);
            if ($this->propertyData->propertable_type === $individualType) {
                // Sale Queue (1 - Venda, 3 - Venda e Aluguel)
                if (in_array($this->propertyData->propertable->role->value, [1, 3])) {
                    $query = clone $baseQuery;
                    $queueData = $query->where('properties_settings', 3) // 3 - Todos os imóveis à venda
                        ->first();

                    if (isset($queueData)) {
                        return $queueData;
                    }
                }

                // Rent Queue (2 - Aluguel)
                if (in_array($this->propertyData->propertable->role->value, [2])) {
                    $query = clone $baseQuery;
                    $queueData = $query->where('properties_settings', 4) // 4 - Todos os imóveis para alugar
                        ->first();

                    if (isset($queueData)) {
                        return $queueData;
                    }
                }
            }

            // 4. Check if the property type is enterprise and try to find
            $enterpriseType = MorphMapByClass(model: PropertyEnterprise::class);
            if ($this->propertyData->propertable_type === $enterpriseType) {
                $query = clone $baseQuery;
                $queueData = $query->where('properties_settings', 5) // 5 - Todos os empreendimentos
                    ->first();

                if (isset($queueData)) {
                    return $queueData;
                }
            }

            // If not found, decrement the order and continue the loop
            $currentOrder = $this->queue->where('role', 1)
                ->where('status', 1)
                ->where('order', '<', $currentOrder)
                ->max('order');
        }

        // Return null if no queue is found among all orders
        return null;
    }

    protected function getQueueByAccountAndCampaignId(array $data): ?Queue
    {
        return $this->queue->where('role', $data['queue_role'])
            ->where('account_id', $data['account_id'])
            ->where('campaign_id', $data['campaign_id'])
            ->where('status', 1) // 1 - Ativo
            ->orderBy('order', 'desc')
            ->first();
    }

    protected function getPropertyByQueue(): ?Property
    {
        return $this->queueData?->properties()
            ->latest()
            ->first();
    }

    protected function getOwnerByQueueDistributionSettings(): User
    {
        $owner = $this->user->where('status', 1) // 1 - Ativo
            ->find($this->propertyData?->user_id);

        if (!$owner) {
            $owner = $this->superadmin;
        }

        if (!$this->queueData) {
            return $owner;
        }

        $distributionSettings = (int) $this->queueData->distribution_settings->value;

        // 1 - Para os captadores
        if ($distributionSettings === 1) {
            return $owner;
        }

        if (isset($this->leadIp)) {
            // Checks if a business already exists for this IP
            $cachedBusinessId = Cache::get("lead_ip:{$this->leadIp}");

            // If the IP was found in the cache
            if ($cachedBusinessId) {
                $cachedBusiness = $this->business->find($cachedBusinessId);

                if ($cachedBusiness) {
                    return $cachedBusiness->currentUser() ?? $owner;
                }
            }
        }

        $users = $this->getUsersByQueueUsersSettings();
        $usersCount = $users->count();

        if ($usersCount === 0) {
            return $owner;
        }

        // 2 - Distribuição alternada
        if ($distributionSettings === 2) {
            $idx = $this->queueData->distribution_index % $usersCount;

            $owner = $users[$idx];

            // Update index for next realtor
            $this->queueData->distribution_index = ($idx + 1) % $usersCount;
            $this->queueData->save();

            return $owner;
        }

        // // 3 - Prioridade por performance
        // if ($distributionSettings === 3) {
        //     //
        // }

        // // 4 - Disponibilidade
        // if ($distributionSettings === 4) {
        //     //
        // }

        return $owner;
    }

    protected function getUsersByQueueUsersSettings(): ?Collection
    {
        $usersSettings = (int) $this->queueData->users_settings->value;

        // 1 - Customizar os usuários
        if ($usersSettings === 1) {
            return $this->queueData->users()
                ->where('status', 1)
                ->get();
        }

        $baseQuery = $this->user->where('status', 1)
            ->whereHas('roles', function (Builder $query): Builder {
                return $query->whereIn('id', [6]); // 6 - Corretor/Realtor
            })
            ->orderBy('id');

        // 2 - Customizar por agências
        if ($usersSettings === 2) {
            $agenciesIds = $this->queueData->agencies()
                ->where('status', 1)
                ->pluck('id');

            return $baseQuery->whereHas('agencies', function (Builder $query) use ($agenciesIds): Builder {
                return $query->whereIn('id', $agenciesIds);
            })
                ->get();
        }

        // 3 - Customizar por equipes
        if ($usersSettings === 3) {
            $teamsIds = $this->queueData->teams()
                ->where('status', 1)
                ->pluck('id');

            return $baseQuery->whereHas('teams', function (Builder $query) use ($teamsIds): Builder {
                return $query->whereIn('id', $teamsIds);
            })
                ->get();
        }

        // 4 - Todos os usuários
        if ($usersSettings === 4) {
            return $baseQuery->get();
        }

        return null;
    }

    protected function createLead(array $data): Individual
    {
        $data['user_id'] = $this->ownerData->id;

        $individual = $this->individual->whereHas('contact', function (Builder $query) use ($data): Builder {
            return $query->where('user_id', $data['user_id'])
                ->where('email', $data['email']);
        })
            ->first();

        // Create contact if not exists
        if (!$individual) {
            $individual = $this->individual->create($data);

            $individual->contact()
                ->create($data);
        }

        $roleToSync = 2; // 2 - Lead

        $hasRole = $individual->contact->roles()
            ->where('role_id', $roleToSync)
            ->exists();

        if (!$hasRole) {
            $individual->contact->roles()
                ->attach($roleToSync);
        }

        return $individual;
    }

    protected function createBusiness(array $data): Business
    {
        $business = $this->business->whereDate('business_at', Carbon::today())
            ->where('source_id', $data['source_id'])
            ->whereHas('contact', function (Builder $query) use ($data): Builder {
                return $query->where('user_id', $this->ownerData->id)
                    ->where('email', $data['email']);
            })
            ->orderBy('created_at', 'desc')
            ->first();

        // Create business if not exists
        if (!$business) {
            $funnelsData = $this->getFunnelsDataByQueueOrProperty();

            $data['funnel_id'] = $funnelsData['funnel']->id;
            $data['funnel_stage_id'] = $funnelsData['stage']->id;
            $data['funnel_substage_id'] = $funnelsData['substage']?->id;

            $data['user_id'] = $this->superadmin->id; // The lead's owner from web is always the superadmin
            $business = $this->business->create($data);

            $data['user_id'] = $this->ownerData->id; // Change owner to the queue user
            $data['business_id'] = $business->id;

            // Attach to the user who will perform the service (owner_id)
            $this->attachUserCollector(business: $business, data: $data);

            // Create business funnel stage
            $this->createBusinessFunnelStage(business: $business, funnelsData: $funnelsData);

            // Create business notification activity
            $this->createBusinessNotificationActivity(data: $data, funnelsData: $funnelsData);

            // => ENVIAR AQUI A NOTIFICAÇÃO VIA BD
            // Send DB notification

            if (isset($this->leadIp)) {
                Cache::put("lead_ip:{$this->leadIp}", $business->id, 86400); // 86400 secs. = 24 hours
            }
        }

        return $business;
    }

    protected function getFunnelsDataByQueueOrProperty(): array
    {
        $funnelId = $this->queueData->funnel_id ?? null;

        if (!$funnelId) {
            $funnelId = 1; // Funil de Vendas

            $individualType = MorphMapByClass(model: PropertyIndividual::class);
            if (
                $this->propertyData->propertable_type === $individualType &&
                $this->propertyData->propertable->role->value === 2
            ) {
                $funnelId = 2; // Funil de Alugueis
            }
        }

        $funnel = $this->funnel->where('id', $funnelId)
            ->first();

        $stage = $funnel->stages()
            ->orderBy('order', 'asc')
            ->first();

        $substage = $stage->substages()
            ->orderBy('order', 'asc')
            ->first();

        return [
            'funnel'   => $funnel,
            'stage'    => $stage,
            'substage' => $substage,
        ];
    }

    protected function attachUserCollector(Business $business, array $data): void
    {
        $business->users()
            ->attach($data['user_id']);
    }

    protected function createBusinessFunnelStage(Business $business, array $funnelsData): void
    {
        $business->businessFunnelStages()
            ->create([
                'funnel_id'          => $funnelsData['funnel']->id,
                'funnel_stage_id'    => $funnelsData['stage']->id,
                'funnel_substage_id' => $funnelsData['substage']?->id ,
                'loss_reason'        => null,
                'business_at'        => now(),
            ]);
    }

    protected function createBusinessNotificationActivity(array $data, array $funnelsData): void
    {
        $description = "Novo negócio criado por {$this->superadmin->name} => {$funnelsData['funnel']->name} / Etapa: {$funnelsData['stage']->name}";

        if ($funnelsData['substage']) {
            $description .= " / Sub-etapa: {$funnelsData['substage']->name}";
        }

        $activityNotification = Notification::create();
        $activity = $activityNotification->activity()
            ->create([
                'description' => $description
            ]);

        $activity->users()
            ->attach($data['user_id']);

        $activity->contacts()
            ->attach($data['contact_id']);

        $activity->business()
            ->attach($data['business_id']);
    }

    protected function syncPropertyToBusiness(array $data): void
    {
        if (isset($this->propertyData)) {
            $this->businessData->properties()
                ->syncWithoutDetaching($this->propertyData->id);

            // Create web conversion activity
            $data['business_id'] = $this->businessData->id;
            $this->createWebConversionActivity(data: $data);
        }
    }

    protected function createWebConversionActivity(array $data): void
    {
        $description = "Conversão realizada no imóvel: {$this->propertyData->code} - {$this->propertyData->title}";

        $activityWebConversion = $this->propertyData->activityWebConversion()
            ->create([
                'data' => [
                    'Nome'     => $data['name'],
                    'Email'    => $data['email'],
                    'Telefone' => $data['phone'],
                    'Mensagem' => $data['message'],
                ]
            ]);

        $activity = $activityWebConversion->activity()
            ->create([
                'description' => $description
            ]);

        $activity->contacts()
            ->attach($data['contact_id']);

        $activity->business()
            ->attach($data['business_id']);
    }

    protected function sendEmailNotification(array $data, array $mailTo): void
    {
        $ownerMail = $this->ownerData->email;
        array_push($mailTo, $ownerMail);

        Mail::to($mailTo)
            ->send(new BusinessLeadFormAlert($data));
    }
}
