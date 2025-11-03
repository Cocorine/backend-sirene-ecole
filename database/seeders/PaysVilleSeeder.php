<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaysVilleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Benin country
        $beninId = Str::ulid();
        DB::table('pays')->insert([
            'id' => $beninId,
            'nom' => 'Bénin',
            'code_iso' => 'BJ', // Assuming 'code' is 'code_iso'
            'indicatif_tel' => '+229',
            'devise' => 'XOF',
            'fuseau_horaire' => 'Africa/Porto-Novo',
            'actif' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Cities for Littoral Department
        $littoralCities = [
            'Cotonou',
        ];

        foreach ($littoralCities as $cityName) {
            DB::table('villes')->insert([
                'id' => Str::ulid(),
                'pays_id' => $beninId,
                'nom' => $cityName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Cities for Atlantique Department
        $atlantiqueCities = [
            'Ouidah',
            'Abomey-Calavi',
            'Allada',
            'Kpomassè',
            'Sô-Ava',
            'Toffo',
            'Tori-Bossito',
            'Zè',
            'Godomey',
        ];

        foreach ($atlantiqueCities as $cityName) {
            DB::table('villes')->insert([
                'id' => Str::ulid(),
                'pays_id' => $beninId,
                'nom' => $cityName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Cities for Ouémé Department
        $ouemeCities = [
            'Adjarra',
            'Adjohoun',
            'Aguégués',
            'Akpro-Missérété',
            'Avrankou',
            'Bonou',
            'Dangbo',
            'Porto-Novo',
            'Sèmè-Kpodji',
        ];

        foreach ($ouemeCities as $cityName) {
            DB::table('villes')->insert([
                'id' => Str::ulid(),
                'pays_id' => $beninId,
                'nom' => $cityName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        }

}
