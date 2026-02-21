<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            // MatiereSeeder::class,
            // NiveauSeeder::class,
            // EcoleSeeder::class,
            EtatSessionSeeder::class,
            // SessionSeeder::class,
            EtatCandidatureSeeder::class,
            RegionCentreSeeder::class,
            UtilisateurSeeder::class,
        ]);
    }
}
