<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // ======================
            // CANDIDATS
            // ======================
            ['libelle_permission' => 'candidats.view', 'description_permission' => 'Voir tous les candidats'],
            ['libelle_permission' => 'candidats.view.own', 'description_permission' => 'Voir son propre profil'],
            ['libelle_permission' => 'candidats.create', 'description_permission' => 'Créer un candidat'],
            ['libelle_permission' => 'candidats.update', 'description_permission' => 'Modifier un candidat'],
            ['libelle_permission' => 'candidats.update.own', 'description_permission' => 'Modifier son propre profil'],
            ['libelle_permission' => 'candidats.delete', 'description_permission' => 'Supprimer un candidat'],

            // ======================
            // CANDIDATURES
            // ======================
            ['libelle_permission' => 'candidatures.view', 'description_permission' => 'Voir toutes les candidatures'],
            ['libelle_permission' => 'candidatures.view.own', 'description_permission' => 'Voir ses propres candidatures'],
            ['libelle_permission' => 'candidatures.create', 'description_permission' => 'Créer une candidature'],
            ['libelle_permission' => 'candidatures.update', 'description_permission' => 'Modifier une candidature'],
            ['libelle_permission' => 'candidatures.update.own', 'description_permission' => 'Modifier sa propre candidature'],
            ['libelle_permission' => 'candidatures.validate', 'description_permission' => 'Valider une candidature'],
            ['libelle_permission' => 'candidatures.reject', 'description_permission' => 'Rejeter une candidature'],
            ['libelle_permission' => 'candidatures.export', 'description_permission' => 'Exporter les candidatures'],

            // ======================
            // CONCOURS
            // ======================
            ['libelle_permission' => 'concours.view', 'description_permission' => 'Voir les concours'],
            ['libelle_permission' => 'concours.create', 'description_permission' => 'Créer un concours'],
            ['libelle_permission' => 'concours.update', 'description_permission' => 'Modifier un concours'],
            ['libelle_permission' => 'concours.delete', 'description_permission' => 'Supprimer un concours'],
            ['libelle_permission' => 'concours.publish', 'description_permission' => 'Publier un concours'],
            ['libelle_permission' => 'concours.close', 'description_permission' => 'Clôturer un concours'],

            // ======================
            // SESSIONS
            // ======================
            ['libelle_permission' => 'sessions.view', 'description_permission' => 'Voir les sessions'],
            ['libelle_permission' => 'sessions.create', 'description_permission' => 'Créer une session'],
            ['libelle_permission' => 'sessions.update', 'description_permission' => 'Modifier une session'],
            ['libelle_permission' => 'sessions.open', 'description_permission' => 'Ouvrir une session'],
            ['libelle_permission' => 'sessions.close', 'description_permission' => 'Fermer une session'],

            // ======================
            // CENTRES
            // ======================
            ['libelle_permission' => 'centres.view', 'description_permission' => 'Voir les centres'],
            ['libelle_permission' => 'centres.create', 'description_permission' => 'Créer un centre'],
            ['libelle_permission' => 'centres.update', 'description_permission' => 'Modifier un centre'],
            ['libelle_permission' => 'centres.assign', 'description_permission' => 'Assigner des centres'],

            // ======================
            // PAIEMENTS
            // ======================
            ['libelle_permission' => 'paiements.view', 'description_permission' => 'Voir tous les paiements'],
            ['libelle_permission' => 'paiements.view.own', 'description_permission' => 'Voir ses propres paiements'],
            ['libelle_permission' => 'paiements.validate', 'description_permission' => 'Valider un paiement'],
            ['libelle_permission' => 'paiements.reject', 'description_permission' => 'Rejeter un paiement'],

            // ======================
            // UTILISATEURS / ROLES
            // ======================
            ['libelle_permission' => 'utilisateurs.view', 'description_permission' => 'Voir les utilisateurs'],
            ['libelle_permission' => 'utilisateurs.create', 'description_permission' => 'Créer un utilisateur'],
            ['libelle_permission' => 'utilisateurs.update', 'description_permission' => 'Modifier un utilisateur'],
            ['libelle_permission' => 'roles.view', 'description_permission' => 'Voir les rôles'],
            ['libelle_permission' => 'permissions.view', 'description_permission' => 'Voir les permissions'],

            // ======================
            // STRUCTURE ACADEMIQUE
            // ======================
            ['libelle_permission' => 'departements.view', 'description_permission' => 'Voir les départements'],
            ['libelle_permission' => 'filieres.view', 'description_permission' => 'Voir les filières'],

            // ======================
            // EPREUVES
            // ======================
            ['libelle_permission' => 'epreuves.view', 'description_permission' => 'Voir les épreuves'],
            ['libelle_permission' => 'epreuves.download', 'description_permission' => 'Télécharger les épreuves'],

            // ======================
            // NOTIFICATIONS
            // ======================
            ['libelle_permission' => 'notifications.view', 'description_permission' => 'Voir les notifications'],
            ['libelle_permission' => 'notifications.send', 'description_permission' => 'Envoyer des notifications'],

            // ======================
            // REPORTS & STATS
            // ======================
            ['libelle_permission' => 'reports.view', 'description_permission' => 'Voir les rapports'],
            ['libelle_permission' => 'reports.export', 'description_permission' => 'Exporter les rapports'],
            ['libelle_permission' => 'statistics.view', 'description_permission' => 'Voir les statistiques'],

            // ======================
            // DASHBOARD
            // ======================
            ['libelle_permission' => 'dashboard.view', 'description_permission' => 'Accéder au tableau de bord'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['libelle_permission' => $permission['libelle_permission']],
                ['description_permission' => $permission['description_permission']]
            );
        }
    }
}
