<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ArboImageMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:arbo-image-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import images Arbo data from CanalPro XML';

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

        // $start = 0; // Índice inicial
        // $limit = 5; // Número máximo de iterações

        // $slicedData = array_slice(array_reverse($data['Listings']['Listing']), $start, $limit);

        foreach (array_reverse($data['Listings']['Listing']) as $key => $item) {
            $idx = $key + 1;

            $property = App\Models\RealEstate\Property::where('code', $item['ListingID'])->first();
            if (!$property) {
                echo "{$idx} - ({$item['ListingID']}) propriedade não encontrada <br/>";
                continue;
            }

            $individual = $property->propertable;

            if (isset($individual->media) && count($individual->media) > 0) {
                continue;
            }

            if (isset($item['Media']['Item']) && is_array($item['Media']['Item'])) {
                foreach ($item['Media']['Item'] as $mediaUrl) {
                    $mediaUrl = trim($mediaUrl);

                    $headers = @get_headers($mediaUrl);

                    if (!empty($mediaUrl) && stripos($headers[0], "200 OK")) {
                        try {
                            $individual->addMediaFromUrl($mediaUrl)
                                ->toMediaCollection('images');
                        } catch (\Exception $e) {
                            echo "Erro ao adicionar mídia: " . $e->getMessage() . "<br/>";
                        }
                    } else {
                        echo "URL não acessível: {$mediaUrl} <br/>";
                    }
                }
            }

            echo "{$idx} - ({$item['ListingID']}) imagens exportadas com sucesso <br/>";
        }

        echo "<br/> Importação de imagens concluída com sucesso.";
    }
}
