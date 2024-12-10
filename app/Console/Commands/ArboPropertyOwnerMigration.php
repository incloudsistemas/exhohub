<?php

namespace App\Console\Commands;

use App\Models\Crm\Contacts\Contact;
use Illuminate\Console\Command;
use App\Models\Crm\Contacts\LegalEntity;
use App\Models\Crm\Contacts\Individual;
use App\Models\RealEstate\Property;
use App\Models\System\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ArboPropertyOwnerMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:arbo-property-owner-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import property owners Arbo data from excel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = storage_path('app/arbo-xls.xlsx');

        Excel::import(new class implements \Maatwebsite\Excel\Concerns\ToCollection
        {
            public function collection(Collection $rows)
            {
                $headers  = $rows->first();
                $dataRows = $rows->slice(1);

                foreach ($dataRows as $row) {

                    $code = $row[2];
                    $owner = $row[18];
                    $realtor = $row[19];

                    $property = Property::where('code', $code)
                        ->first();

                    if ($property) {
                        $contact = Contact::where('name', $owner)
                            ->first();

                        if (!$contact) {
                            if (in_array($owner, ['EXHO Aluguel', 'EXHO Aluguel 2'])) {
                                $contactable = LegalEntity::create([]);
                            } else {
                                $contactable = Individual::create([]);
                            }

                            $contact = $contactable->contact()
                                ->create([
                                    'user_id'   => 1,
                                    'source_id' => 12, // 'Migração Arbo'
                                    'name'      => $owner,
                                ]);

                            $contact->roles()
                                ->sync(4); // 4 - Proprietário
                        }

                        $property->contacts()
                            ->sync([$contact->id => ['role' => 1]]);

                        echo "{$code} - ({$property->title}) - Contato {$contact->name} migrado com sucesso <br/>";

                        $user = User::where('name', $realtor)
                            ->first();

                        if (!$user) {
                            $user = User::create([
                                'name'              => $realtor,
                                'email'             => Str::slug($realtor) . '@exho.com.br',
                                'email_verified_at' => now(),
                                'password'          => Hash::make('corretor@exho'),
                                'remember_token'    => Str::random(10),
                                'status'            => 2
                            ]);

                            $user->assignRole('Corretor');
                        }

                        $property->update(['user_id' => $user->id]);

                        echo "Usuário {$user->name} migrado com sucesso <br/>";
                    }
                }
            }
        }, $filePath);

        echo "<br/> Importação de proprietários e captadores concluída com sucesso.";
    }
}
