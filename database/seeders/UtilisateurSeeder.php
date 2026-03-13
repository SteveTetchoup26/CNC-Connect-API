<?php

namespace Database\Seeders;

use App\Models\Administrateur;
use App\Models\Candidat;
use App\Models\Role;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UtilisateurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $adminUser = Utilisateur::create([
                'email' => 'admin@test.com',
                'password' => Hash::make('123456'),
                'nom' => 'ADMIN',
                'prenom' => 'SYSTEME',
                'telephone' => '690000001',
            ]);

            $roleAdmin = Role::where('libelle', 'ADMINISTRATEUR')->first();

            if (!$roleAdmin) {
                throw new \Exception('Rôle ADMINISTRATEUR introuvable');
            }

            $adminUser->roles()->attach($roleAdmin->id);

            Administrateur::create([
                'utilisateur_id' => $adminUser->id,
                'matricule' => 'ADM-0001',
                'departement' => 'SYSTÈME D’INFORMATION',
            ]);


            // $utilisateur = Utilisateur::create([
            //     'email' => 'candidat@test.com',
            //     'password' => Hash::make('123456'),
            //     'nom' => 'DOE',
            //     'prenom' => 'JOHN',
            //     'telephone' => '690000002',
            // ]);

            // // Récupérer le rôle "CANDIDAT"
            // $roleCandidat = Role::where('libelle', 'CANDIDAT')->first();

            // if (!$roleCandidat) {
            //     throw new \Exception('Rôle CANDIDAT introuvable');
            // }

            // // Assigner le rôle candidat
            // $utilisateur->roles()->attach($roleCandidat->id);

            // // Créer le candidat
            // Candidat::create([
            //     'utilisateur_id' => $utilisateur->id,
            //     'adresse' => null,
            //     'nationalite' => null,
            //     'sexe' => null,
            //     'date_naissance' => null,
            //     'lieu_naissance' => null,
            //     'region_origine' => null,
            //     'ville_origine' => null,
            //     'departement_origine' => null,
            //     'nom_mere' => null,
            //     'telephone_mere' => null,
            //     'nom_pere' => null,
            //     'telephone_pere' => null,
            //     'niveau_scolaire' => null,
            // ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Erreur Seeder UtilisateurAdminCandidatSeeder', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
