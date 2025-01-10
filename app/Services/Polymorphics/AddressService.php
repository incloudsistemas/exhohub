<?php

namespace App\Services\Polymorphics;

use App\Models\Polymorphics\Address;
use App\Services\BaseService;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;

class AddressService extends BaseService
{
    public function __construct(protected Address $address)
    {
        //
    }

    public function setUniqueMainAddress(array $data, ?Address $address, RelationManager $livewire): void
    {
        if ($data['is_main']) {
            $livewire->ownerRecord->addresses()
                ->where('is_main', true)
                ->when($address, function ($query) use ($address) {
                    return $query->where('id', '<>', $address->id);
                })
                ->update(['is_main' => false]);
        }
    }

    public function getAddressByZipcodeViaCep(?string $zipcode): array
    {
        // Return empty values ​​if zip code is not provided
        if (!$zipcode) {
            return ["error" => "CEP não fornecido. Por favor, preencha o CEP."];
        }

        $zipcode = preg_replace('/\D/', '', $zipcode);

        // Validate the number of characters in the ZIP code
        if (strlen($zipcode) != 8) {
            return ['error' => __('CEP inválido. O CEP deve conter 8 números.')];
        }

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('GET', "https://viacep.com.br/ws/{$zipcode}/json/");

            if ($response->getStatusCode() === 200) {
                $address = json_decode($response->getBody()->getContents(), true);

                // Check if the API returned any errors (e.g.: CEP not found)
                if (isset($address['erro']) && $address['erro'] === 'true') {
                    return ['error' => __('CEP não encontrado. Por favor, verifique o CEP informado.')];
                }

                return $address;
            } else {
                return ["error" => __('Erro ao consultar a API')];
            }
        } catch (\Exception $e) {
            return ['error' => __('Falha ao buscar endereço. Por favor, tente novamente mais tarde.')];
        }
    }

    /**
     * $action can be:
     * Filament\Tables\Actions\DeleteAction;
     * Filament\Actions\DeleteAction;
     */
    public function preventAddressDeleteIf($action, Address $address, RelationManager $livewire): void
    {
        if ($address->is_main && $livewire->ownerRecord->addresses->count() > 1) {
            Notification::make()
                ->title(__('Ação proibida: Exclusão de endereço principal'))
                ->warning()
                ->body(__('Não é possível excluir o endereço principal porque há outros endereços cadastrados. Para excluir este endereço, você deve primeiro definir outro endereço como principal.'))
                ->send();

            // $action->cancel();
            $action->halt();
        }
    }
}
