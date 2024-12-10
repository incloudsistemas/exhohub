<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ArboDataMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:arbo-data-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Arbo data from CanalPro XML';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        set_time_limit(0);

        $filePath = storage_path('app/vivareal-xml.xml');

        if (!file_exists($filePath)) {
            return response()->json(['error' => "File not found: $filePath"], 404);
        }

        $xmlContent = file_get_contents($filePath);
        $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($xml);
        $data = json_decode($json, true);

        // dd($data['Listings']['Listing']);

        foreach (array_reverse($data['Listings']['Listing']) as $key => $item) {
            // dd($item);

            $idx = $key + 1;

            $slug = Illuminate\Support\Str::slug($item['Title']);

            $p = App\Models\RealEstate\Property::where('slug', $slug)
                ->first();

            if ($p !== null) {
                $slug = $slug . '-' . $key;
            }

            $item['TransactionType'] = match ($item['TransactionType']) {
                'For Sale'  => 1,
                'For Rent'  => 2,
                'Sale/Rent' => 3,
            };

            // dd($item['TransactionType']);

            $propertyType = explode('/', $item['Details']['PropertyType']);
            $propertyType = array_map('trim', $propertyType);
            $propertyType = isset($propertyType[1]) ? $propertyType[1] : '';
            $propertyType = App\Models\RealEstate\PropertyType::where('canal_pro_vrsync', $propertyType)
                ->first(['id']);

            if ($propertyType === null) {
                // echo "{$idx} - [ERRO Type] ({$item['ListingID']}) não foi encontrado o tipo: {$item['Details']['PropertyType']} <br/>";
                // continue;

                $item['Details']['PropertyType'] = match ($item['Details']['PropertyType']) {
                    'Apartamento'           => 1, // Apartamento
                    'Commercial / Building' => 2, // Casa
                };
            } else {
                $item['Details']['PropertyType'] = $propertyType->id;
            }

            // dd($item['Details']['PropertyType']);

            // if (!isset($item['Details']['UsageType'])) {
            //     echo "{$idx} - [ERRO Usage] ({$item['ListingID']}) não foi encontrado a finalidade <br/>";
            //     continue;
            // }

            if (isset($item['Details']['UsageType'])) {
                $item['Details']['UsageType'] = match ($item['Details']['UsageType']) {
                    'Residential' => 1,
                    'Commercial'  => 2,
                };
            } else {
                $item['Details']['UsageType'] = 1; // Residential
            }

            // dd($item['Details']['UsageType']);

            // dd(Carbon::parse($item['ListDate']));

            $characteristicsData = [];
            if (isset($item['Details']['Features']) && !empty($item['Details']['Features'])) {
                foreach ($item['Details']['Features'] as $feature) {
                    $characteristic = App\Models\RealEstate\PropertyCharacteristic::where('canal_pro_vrsync', $feature)
                        ->first(['id']);

                    if ($characteristic !== null) {
                        $characteristicsData[] = $characteristic->id;
                    }
                }
            }

            // 1 - 'Completo', 2 - 'Somente bairro, cidade e uf', 3 - 'Somente rua, cidade e uf', 4 - 'Somente cidade e uf'.
            $item['Location']['@attributes']['displayAddress'] = match ($item['Location']['@attributes']['displayAddress']) {
                // Somente bairro, cidade e uf
                'Neighborhood' => '2',
                // Somente rua, cidade e uf
                'Street' => '3',
                    // Completo
                default => '1',
            };

            $individualData = [
                'role'            => $item['TransactionType'],
                'sale_price'      => isset($item['Details']['ListPrice']) ? (int) ($item['Details']['ListPrice'] * 100) : null,
                'rent_price'      => isset($item['Details']['RentalPrice']) ? (int) ($item['Details']['RentalPrice'] * 100) : null,
                'rent_period'     => isset($item['Details']['RentalPrice']) ? 3 : null, // 3 - Monthly
                'rent_warranties' => null,
                'useful_area'     => isset($item['Details']['LivingArea']) ? (int) ($item['Details']['LivingArea'] * 100) : null,
                'total_area'      => isset($item['Details']['LotArea']) ? (int) ($item['Details']['LotArea'] * 100) : null,
                'bedroom'         => $item['Details']['Bedrooms'] ?? null,
                'suite'           => $item['Details']['Suites'] ?? null,
                'bathroom'        => $item['Details']['Bathrooms'] ?? null,
                'garage'          => $item['Details']['garage'] ?? null,
            ];

            $propertyData = [
                // 'propertable_type' => null,
                // 'propertable_id'   => null,
                'type_id'          => $item['Details']['PropertyType'],
                'subtype_id'       => null,
                'user_id'          => null,
                'usage'            => $item['Details']['UsageType'],
                'code'             => $item['ListingID'],
                'title'            => $item['Title'],
                'slug'             => $slug,
                'subtitle'         => null,
                'excerpt'          => null,
                'body'             => isset($item['Details']['Description']) ? (is_array($item['Details']['Description']) ? implode(' ', array_map('trim', $item['Details']['Description'])) : trim(htmlspecialchars($item['Details']['Description'], ENT_QUOTES, 'UTF-8'))) : null,
                'owner_notes'      => null,
                'url'              => $item['VirtualTourLink'] ?? null,
                'embed_videos'     => null,
                'show_address'     => $item['Location']['@attributes']['displayAddress'],
                'show_watermark'   => 0,
                'standard'         => null,
                'tax_price'        => isset($item['Details']['YearlyTax']) ? (int) ($item['Details']['YearlyTax'] * 100) : null,
                'condo_price'      => isset($item['Details']['PropertyAdministrationFee']) ? (int) ($item['Details']['PropertyAdministrationFee'] * 100) : null,
                'floors'           => $item['Details']['Floors'] ?? null,
                'units_per_floor'  => $item['Details']['UnitFloor'] ?? null,
                'towers'           => $item['Details']['Buildings'] ?? null,
                'construct_year'   => $item['Details']['YearBuilt'] ?? null,
                'publish_on'       => ["canal_pro" => true, "portal_exho" => true, "portal_web" => true],
                'publish_on_data'  => ["canal_pro" => ["publication_type" => $item['PublicationType']]],
                // 'tags'             => null,
                'order'            => 1,
                'featured'         => 1,
                // 'comment'          => null,
                // 'meta_title'       => null,
                // 'meta_description' => null,
                // 'meta_keywords'    => null,
                'publish_at'       => Illuminate\Support\Carbon::parse($item['ListDate']),
                'expiration_at'    => null,
                'created_at'       => Illuminate\Support\Carbon::parse($item['ListDate']),
                // 'updated_at'       => Illuminate\Support\Carbon::parse($item['LastUpdateDate']),
            ];

            $addressData = [
                // 'addressable_type' => null,
                // 'addressable_id'   => null,
                'name'             => null,
                'is_main'          => 1,
                'zipcode'          => $item['Location']['PostalCode'] ?? null,
                'state'            => null,
                'uf'               => $item['Location']['State'] ?? null,
                'city'             => $item['Location']['City'] ?? null,
                'country'          => 'Brasil',
                'district'         => $item['Location']['Neighborhood'] ?? null,
                'address_line'     => $item['Location']['Address'] ?? null,
                'number'           => $item['Location']['StreetNumber'] ?? null,
                'complement'       => $item['Location']['Complement'] ?? null,
                'custom_street'    => null,
                'custom_block'     => null,
                'custom_lot'       => null,
                'reference'        => null,
                'gmap_coordinates' => ["latitude" => $item['Location']['Latitude'] ?? null, "longitude" => $item['Location']['Longitude'] ?? null],
                'created_at'       => Illuminate\Support\Carbon::parse($item['ListDate']),
                // 'updated_at'       => Illuminate\Support\Carbon::parse($item['LastUpdateDate']),
            ];

            $individual = App\Models\RealEstate\Individual::create($individualData);

            $property = $individual->property()
                ->create($propertyData);

            $property->characteristics()
                ->attach($characteristicsData);

            $property->address()
                ->create($addressData);

            // if (isset($item['Media']['Item']) && is_array($item['Media']['Item'])) {
            //     foreach ($item['Media']['Item'] as $mediaUrl) {
            //         $mediaUrl = trim($mediaUrl);

            //         if (!empty($mediaUrl)) {
            //             $individual->addMediaFromUrl($mediaUrl)
            //                 ->toMediaCollection('images');
            //         }
            //     }
            // }

            echo "{$idx} - ({$item['ListingID']}) {$item['Title']} criado com sucesso <br/>";
        }

        echo "<br/> Importação de dados concluída com sucesso.";
    }
}
