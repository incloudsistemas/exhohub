<?php

namespace App\Services\Web;

use App\Mail\Web\ContactUsForm;
use App\Mail\Web\NewsletterSubscribeFormAlert;
use App\Mail\Web\WorkWithUsForm;
use App\Models\Crm\Contacts\Individual;
use App\Models\Crm\Source;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MailUsService extends BaseFormService
{
    protected ?Individual $individualData;

    public function __construct()
    {
        //
    }

    public function create(array $data, string $role, array $mailTo, ?string $recaptchaSecret = null)
    {
        DB::beginTransaction();

        try {
            $this->honeyPotCheckBot($data);
            $this->reCaptchaProtection($data, $recaptchaSecret);

            $customMessages = $data['custom_messages'] ?? array();
            $this->setCustomMessages($customMessages);

            $data = $this->mutateFormDataBeforeCreate(data: $data);

            $this->individualData = $this->saveSubscriber(data: $data, role: $role);

            $this->sendEmail(data: $data, mailTo: $mailTo, role: $role);

            DB::commit();

            return [
                'success'   => true,
                'from'      => 'web',
                'message'   => $this->message['success'],
                'data'      => $this->individualData,
                'fbq_track' => $data['fbq_track'] ?? null,
            ];
        } catch (\Exception $e) {
            DB::rollback();

            return $this->getErrorException($e);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['phone'])) {
            $data['phones'] = [[
                'name'   => null,
                'number' => $data['phone']
            ]];
        }


        $sourceId = 1; // 1 - Website
        $source = Source::where('id', $sourceId)
            ->where('status', 1) // 1 - Ativo/Active
            ->first();

        $data['source_id'] = $source?->id ?? null;

        return $data;
    }

    protected function saveSubscriber(array $data, string $role): ?Individual
    {
        if ($role !== 'newsletter-subscribe') {
            return null;
        }

        $individuals = $this->individual->whereHas('contact', function (Builder $query) use ($data): Builder {
            return $query->where('email', $data['email']);
        })
            ->get();

        $roleToSync = 1; // 1- Assinante/Subscriber

        if ($individuals->isEmpty()) {
            $individual = $this->individual->create($data);

            $individual->contact()
                ->create($data);

            $individual->contact->roles()
                ->attach($roleToSync);
        } else {
            foreach ($individuals as $individual) {
                $hasRole = $individual->contact->roles()
                    ->where('role_id', $roleToSync)
                    ->exists();

                if (!$hasRole) {
                    $individual->contact->roles()
                        ->attach($roleToSync);
                }
            }

            $individual = $individuals->last();
        }

        return $individual;
    }

    protected function sendEmail(array $data, array $mailTo, string $role): void
    {
        $mailClass = match ($role) {
            'contact-us'           => ContactUsForm::class,
            'work-with-us'         => WorkWithUsForm::class,
            'newsletter-subscribe' => NewsletterSubscribeFormAlert::class,
            default                => throw new \Exception('Error. => ' . $this->message['error_unexpected']),
        };

        Mail::to($mailTo)
            ->send(new $mailClass($data));
    }
}
