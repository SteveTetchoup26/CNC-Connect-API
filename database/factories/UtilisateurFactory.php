<?php

namespace Database\Factories;

use App\Models\Utilisateur;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Utilisateur>
 */
class UtilisateurFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
     protected $model = Utilisateur::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'telephone' => $this->faker->phoneNumber(),
        ];
    }

    /**
     * État ADMINISTRATEUR
     */
    public function admin(): static
    {
        return $this->afterCreating(function (Utilisateur $user) {
            $role = \App\Models\Role::firstOrCreate(
                ['libelle' => 'ADMINISTRATEUR'],
                ['description' => 'Administrateur du système']
            );

            $user->roles()->attach($role->id);
        });
    }

    /**
     * État CANDIDAT
     */
    public function candidat(): static
    {
        return $this->afterCreating(function (Utilisateur $user) {
            $role = \App\Models\Role::firstOrCreate(
                ['libelle' => 'CANDIDAT'],
                ['description' => 'Candidat']
            );

            $user->roles()->attach($role->id);
        });
    }
}
