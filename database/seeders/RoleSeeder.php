<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Créer les rôles
        $superAdmin = Role::create([
            'libelle_role' => 'super_admin',
            'description_role' => 'Super Administrateur avec tous les droits'
        ]);

        $admin = Role::create([
            'libelle_role' => 'admin',
            'description_role' => 'Administrateur du système'
        ]);

        $gestionnaire = Role::create([
            'libelle_role' => 'gestionnaire',
            'description_role' => 'Gestionnaire des concours et candidatures'
        ]);

        $candidat = Role::create([
            'libelle_role' => 'candidat',
            'description_role' => 'Candidat aux concours'
        ]);

        // Assigner toutes les permissions au Super Admin
        $allPermissions = Permission::all();
        foreach ($allPermissions as $permission) {
            $superAdmin->permissions()->attach($permission->id);
        }

        // Permissions pour Admin
        $adminPermissions = [
            'concours.view', 'concours.create', 'concours.update', 'concours.delete', 'concours.publish', 'concours.close',
            'candidatures.view', 'candidatures.validate', 'candidatures.reject', 'candidatures.export',
            'sessions.view', 'sessions.create', 'sessions.update', 'sessions.open', 'sessions.close',
            'centres.view', 'centres.create', 'centres.update', 'centres.assign',
            'paiements.view', 'paiements.validate', 'paiements.reject',
            'utilisateurs.view', 'utilisateurs.create', 'utilisateurs.update',
            'roles.view', 'permissions.view',
            'notifications.view', 'notifications.send',
            'departements.view', 'filieres.view',
            'reports.view', 'reports.export', 'statistics.view', 'dashboard.view'
        ];

        foreach ($adminPermissions as $permissionName) {
            $permission = Permission::where('libelle_permission', $permissionName)->first();
            if ($permission) {
                $admin->permissions()->attach($permission->id);
            }
        }

        // Permissions pour Gestionnaire
        $gestionnairePermissions = [
            'concours.view', 'concours.update',
            'candidatures.view', 'candidatures.validate', 'candidatures.reject', 'candidatures.export',
            'sessions.view',
            'centres.view', 'centres.assign',
            'paiements.view', 'paiements.validate',
            'notifications.view', 'notifications.send',
            'reports.view', 'statistics.view', 'dashboard.view'
        ];

        foreach ($gestionnairePermissions as $permissionName) {
            $permission = Permission::where('libelle_permission', $permissionName)->first();
            if ($permission) {
                $gestionnaire->permissions()->attach($permission->id);
            }
        }

        // Permissions pour Candidat
        $candidatPermissions = [
            'candidats.view.own', 'candidats.update.own',
            'candidatures.view.own', 'candidatures.create', 'candidatures.update.own',
            'concours.view',
            'paiements.view.own',
            'epreuves.view', 'epreuves.download',
            'centres.view',
            'filieres.view',
            'departements.view'
        ];

        foreach ($candidatPermissions as $permissionName) {
            $permission = Permission::where('libelle_permission', $permissionName)->first();
            if ($permission) {
                $candidat->permissions()->attach($permission->id);
            }
        }
    }
}
