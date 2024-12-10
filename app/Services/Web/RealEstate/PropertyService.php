<?php

namespace App\Services\Web\RealEstate;

use App\Models\RealEstate\Enterprise;
use App\Models\RealEstate\Individual;
use App\Models\RealEstate\Property;
use App\Models\RealEstate\PropertySearch;
use App\Models\RealEstate\PropertyType;
use App\Rules\FloatPtBrFormatRule;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PropertyService extends BaseService
{
    protected array $message = [
        'success'           => 'Recebemos com sucesso o seu cadastro e entraremos em contato contigo assim que possível.',
        'error'             => 'O cadastro não pôde ser efetuado devido a algum erro inesperado. Por favor, tente novamente mais tarde.',
        'error_bot'         => 'Bot detectado! O formulário não pôde ser processado! Por favor, tente novamente!',
        'error_unexpected'  => 'Ocorreu um erro inesperado. Por favor, tente novamente mais tarde.',
        'recaptcha_invalid' => 'Captcha não validado! Por favor, tente novamente!',
        'recaptcha_error'   => 'Captcha não enviado! Por favor, tente novamente.',
    ];

    public function __construct(
        protected Property $property,
        protected PropertySearch $propertySearch,
    ) {
        //
    }

    public function searchWebIndividuals(
        array $data,
        array $roles = [1, 2, 3],
        array $statuses = [1,],
        string $orderBy = 'order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        $data = $this->mutateFormDataBeforeSearch(data: $data);

        $query = $this->property->getWeb(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )->whereHasMorph(
            'propertable',
            [Individual::class],
            function ($query) use ($data, $roles): Builder {
                $query->whereIn('role', $roles);

                if (isset($data['min_price']) && !empty($data['min_price'])) {
                    $query->where('sale_price', '>=', $data['min_price']);
                }

                if (isset($data['max_price']) && !empty($data['max_price'])) {
                    $query->where('sale_price', '<=', $data['max_price']);
                }

                if (isset($data['bedroom']) && !empty($data['bedroom'])) {
                    if ((int) $data['bedroom'] < 4) {
                        $query->where('bedroom', $data['bedroom']);
                    } else {
                        $query->where('bedroom', '>=', $data['bedroom']);
                    }
                }

                if (isset($data['bathroom']) && !empty($data['bathroom'])) {
                    if ((int) $data['bathroom'] < 4) {
                        $query->where('bathroom', $data['bathroom']);
                    } else {
                        $query->where('bathroom', '>=', $data['bathroom']);
                    }
                }

                if (isset($data['garage']) && !empty($data['garage'])) {
                    if ((int) $data['garage'] < 4) {
                        $query->where('garage', $data['garage']);
                    } else {
                        $query->where('garage', '>=', $data['garage']);
                    }
                }

                if (isset($data['min_useful_area']) && !empty($data['min_useful_area'])) {
                    $query->where('useful_area', '>=', $data['min_useful_area']);
                }

                if (isset($data['max_useful_area']) && !empty($data['max_useful_area'])) {
                    $query->where('useful_area', '<=', $data['max_useful_area']);
                }

                return $query;
            }
        );

        $this->applyTypesLocationAndCodeFilters(query: $query, data: $data);

        return $query;
    }

    public function searchWebEnterprises(
        array $data,
        array $roles = [1, 2, 3],
        array $statuses = [1,],
        string $orderBy = 'order',
        string $direction = 'desc',
        string $publishAtDirection = 'desc'
    ): Builder {
        $data = $this->mutateFormDataBeforeSearch(data: $data);

        $query = $this->property->getWeb(
            statuses: $statuses,
            orderBy: $orderBy,
            direction: $direction,
            publishAtDirection: $publishAtDirection
        )
            ->whereHasMorph(
                'propertable',
                [Enterprise::class],
                function ($query) use ($data, $roles): Builder {
                    $data['enterprise_role'] = isset($data['enterprise_role'])
                        ? [$data['enterprise_role']]
                        : $roles;

                    $query->whereIn('role', $data['enterprise_role']);

                    if (isset($data['min_price']) && !empty($data['min_price'])) {
                        $query->where(function ($query) use ($data): Builder {
                            return $query->where('min_price', '>=', $data['min_price'])
                                ->orWhere('max_price', '>=', $data['min_price']);
                        });
                    }

                    if (isset($data['max_price']) && !empty($data['max_price'])) {
                        $query->where(function ($query) use ($data): Builder {
                            return $query->where('min_price', '<=', $data['max_price'])
                                ->orWhere('max_price', '<=', $data['max_price']);
                        });
                    }

                    if (isset($data['bedroom']) && !empty($data['bedroom'])) {
                        if ((int) $data['bedroom'] < 4) {
                            $query->where(function ($query) use ($data): Builder {
                                return $query->where('min_bedroom', '<=', $data['bedroom'])
                                    ->where('max_bedroom', '>=', $data['bedroom']);
                            });
                        } else {
                            $query->where(function ($query) use ($data): Builder {
                                return $query->where('min_bedroom', '>=', $data['bedroom'])
                                    ->orWhere('max_bedroom', '>=', $data['bedroom']);
                            });
                        }
                    }

                    if (isset($data['bathroom']) && !empty($data['bathroom'])) {
                        if ((int) $data['bathroom'] < 4) {
                            $query->where(function ($query) use ($data): Builder {
                                return $query->where('min_bathroom', '<=', $data['bathroom'])
                                    ->where('max_bathroom', '>=', $data['bathroom']);
                            });
                        } else {
                            $query->where(function ($query) use ($data): Builder {
                                return $query->where('min_bathroom', '>=', $data['bathroom'])
                                    ->orWhere('max_bathroom', '>=', $data['bathroom']);
                            });
                        }
                    }

                    if (isset($data['garage']) && !empty($data['garage'])) {
                        if ((int) $data['garage'] < 4) {
                            $query->where(function ($query) use ($data): Builder {
                                return $query->where('min_garage', '<=', $data['garage'])
                                    ->where('max_garage', '>=', $data['garage']);
                            });
                        } else {
                            $query->where(function ($query) use ($data): Builder {
                                return $query->where('min_garage', '>=', $data['garage'])
                                    ->orWhere('max_garage', '>=', $data['garage']);
                            });
                        }
                    }

                    if (isset($data['min_useful_area']) && !empty($data['min_useful_area'])) {
                        $query->where(function ($query) use ($data): Builder {
                            return $query->where('min_useful_area', '>=', $data['min_useful_area'])
                                ->orWhere('max_useful_area', '>=', $data['min_useful_area']);
                        });
                    }

                    if (isset($data['max_useful_area']) && !empty($data['max_useful_area'])) {
                        $query->where(function ($query) use ($data): Builder {
                            return $query->where('min_useful_area', '<=', $data['max_useful_area'])
                                ->orWhere('max_useful_area', '<=', $data['max_useful_area']);
                        });
                    }

                    return $query;
                }
            );

        $this->applyTypesLocationAndCodeFilters(query: $query, data: $data);

        if (isset($data['enterprise_title']) && !empty($data['enterprise_title'])) {
            $query->where('title', 'like', '%' . $data['enterprise_title'] . '%');
        }

        return $query;
    }

    protected function applyTypesLocationAndCodeFilters(Builder &$query, array $data)
    {
        if (isset($data['types'])) {
            $query->where(function ($query) use ($data): Builder {
                if (!empty($data['types']['residencial'])) {
                    $query->orWhere(function ($query) use ($data) {
                        $query->whereIn('type_id', $data['types']['residencial'])
                            ->where('usage', 1); // Residencial
                    });
                }

                if (!empty($data['types']['comercial'])) {
                    $query->orWhere(function ($query) use ($data) {
                        $query->whereIn('type_id', $data['types']['comercial'])
                            ->where('usage', 2); // Comercial
                    });
                }

                return $query;
            });
        }

        if (isset($data['location']) && !empty($data['location'])) {
            $query->where(function ($query) use ($data): Builder {
                return $query->whereHas('address', function ($query) use ($data): Builder {
                    return $query->where('address_line', 'like', '%' . $data['location'] . '%')
                        ->orWhere('district', 'like', '%' . $data['location'] . '%')
                        ->orWhere('city', 'like', '%' . $data['location'] . '%');
                })
                    ->orWhere('title', 'like', '%' . $data['location'] . '%');
            });
        }

        if (isset($data['code']) && !empty($data['code'])) {
            $query->where('code', $data['code']);
        }
    }

    public function createSearch(array $data, ?string $recaptchaSecret = null)
    {
        // $data['user_ip'] = request()->ip();
        $data['user_ip'] = $_SERVER['REMOTE_ADDR'];

        $cacheKey = 'request_count_' . $data['user_ip'];

        $requestCount = Cache::get($cacheKey, 0);

        // More than 30 requests from the same IP, simulate success request
        if ($requestCount >= 30) {
            $this->data = true;

            return [
                'success'   => true,
                'from'      => 'web',
                'message'   => 'Pesquisa criada com sucesso.',
                'data'      => $this->data,
                'fbq_track' => $data['fbq_track'] ?? null,
            ];
        }

        DB::beginTransaction();

        try {
            $this->honeyPotCheckBot($data);
            $this->reCaptchaProtection($data, $recaptchaSecret);

            $rules = [
                // 1 - 'Imóveis à venda', 2 - 'Imóveis para alugar', 3 - 'Lançamentos'.
                'role'              => 'required|max:255',
                // 'Apartamento', 'Área/Lote', 'Condomínio', 'Casa residencial'...
                'types'             => 'nullable|array',
                'code'              => 'nullable|max:255',
                // 1 - 'Na planta', 2 - 'Em construção', 3 - 'Pronto pra morar'.
                'enterprise_role'   => 'nullable|integer',
                'enterprise_name'   => 'nullable|max:255',
                'location'          => 'nullable|max:255',
                'min_price'         => ['nullable', new FloatPtBrFormatRule],
                'max_price'         => ['nullable', new FloatPtBrFormatRule],
                'min_useful_area'   => ['nullable', new FloatPtBrFormatRule],
                'max_useful_area'   => ['nullable', new FloatPtBrFormatRule],
                'min_total_area'    => ['nullable', new FloatPtBrFormatRule],
                'max_total_area'    => ['nullable', new FloatPtBrFormatRule],
                'bedroom'           => 'nullable|integer',
                'suite'             => 'nullable|integer',
                'bathroom'          => 'nullable|integer',
                'garage'            => 'nullable|integer',
                '_token'            => 'nullable|max:255',
            ];

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                throw new \Illuminate\Validation\ValidationException($validator);
            }

            $data = $this->mutateFormDataBeforeSearch(data: $data);
            $data['types'] = $data['display_types'] ?? [];

            $this->data = $this->propertySearch->create($data);

            Cache::put($cacheKey, $requestCount + 1, now()->addDay());

            DB::commit();

            return [
                'success'   => true,
                'from'      => 'web',
                'message'   => 'Pesquisa criada com sucesso.',
                'data'      => $this->data,
                'fbq_track' => $data['fbq_track'] ?? null,
            ];
        } catch (\Exception $e) {
            DB::rollback();

            return $this->getErrorException($e);
        }
    }

    protected function honeyPotCheckBot(array $data): void
    {
        $prefix = isset($data['prefix']) ? $data['prefix'] : '';

        $botcheck = isset($data[$prefix . 'botcheck']) ? $data[$prefix . 'botcheck'] : '';

        if (!empty($botcheck)) {
            throw new \Exception('Error. => ' . $this->message['error_bot']);
        }

        return;
    }

    protected function reCaptchaProtection(array $data, ?string $recaptchaSecret): void
    {
        if (isset($data['g-recaptcha-response']) && isset($recaptchaSecret)) {
            $recaptchaData = [
                'secret'   => $recaptchaSecret,
                'response' => $data['g-recaptcha-response']
            ];

            $recapVerify = curl_init();

            curl_setopt($recapVerify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
            curl_setopt($recapVerify, CURLOPT_POST, true);
            curl_setopt($recapVerify, CURLOPT_POSTFIELDS, http_build_query($recaptchaData));
            curl_setopt($recapVerify, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($recapVerify, CURLOPT_RETURNTRANSFER, true);

            $recapResponse = curl_exec($recapVerify);

            $gResponse = json_decode($recapResponse);

            if ($gResponse->success !== true) {
                throw new \Exception('Error. => ' . $this->message['recaptcha_invalid']);
            }
        }

        $forceRecap = (!empty($data['force_recaptcha']) && $data['force_recaptcha'] !== false) ? true : false;

        if ($forceRecap) {
            if (!isset($data['g-recaptcha-response'])) {
                throw new \Exception('Error. => ' . $this->message['recaptcha_error']);
            }
        }

        return;
    }

    protected function mutateFormDataBeforeSearch(array $data): array
    {
        $data['role'] = match ($data['role']) {
            'a-venda'     => 'Imóveis à venda',
            'para-alugar' => 'Imóveis para alugar',
            'lancamentos' => 'Lançamentos',
        };

        $data['min_price'] = isset($data['min_price']) ? ConvertPtBrFloatStringToInt($data['min_price']) : null;
        $data['max_price'] = isset($data['max_price']) ? ConvertPtBrFloatStringToInt($data['max_price']) : null;

        $data['min_useful_area'] = isset($data['min_useful_area'])
            ? ConvertPtBrFloatStringToInt($data['min_useful_area'])
            : null;

        $data['max_useful_area'] = isset($data['max_useful_area'])
            ? ConvertPtBrFloatStringToInt($data['max_useful_area'])
            : null;

        if (isset($data['types'])) {
            $dividedTypes = [
                "residencial" => [],
                "comercial" => []
            ];

            foreach ($data['types'] as $type) {
                list($id, $category) = explode('_', $type);

                if (strpos($category, 'residencial') !== false) {
                    $dividedTypes['residencial'][] = (int) $id;
                } elseif (strpos($category, 'comercial') !== false) {
                    $dividedTypes['comercial'][] = (int) $id;
                }
            }

            $data['types'] = $dividedTypes;

            if (!empty($data['types']['residencial'])) {
                $residentialNames = PropertyType::whereIn('id', $data['types']['residencial'])
                    ->pluck('name')
                    ->toArray();

                $data['display_types']['Residencial'] = $residentialNames;
            }

            if (!empty($data['types']['comercial'])) {
                $commercialNames = PropertyType::whereIn('id', $data['types']['comercial'])
                    ->pluck('name')
                    ->toArray();

                $data['display_types']['Comercial'] = $commercialNames;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSendAnnounce(array $data): array
    {
        $typeParts = explode('_', $data['type']);
        $data['type'] = ucfirst(end($typeParts));

        return $data;
    }
}
