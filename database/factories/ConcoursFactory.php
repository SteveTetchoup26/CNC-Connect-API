<?php

namespace Database\Factories;

use App\Models\Concours;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConcoursFactory extends Factory
{
    protected $model = Concours::class;

    public function definition()
    {
        $dateLimite = $this->faker->dateTimeBetween('now', '+1 month');
        $dateExamen = $this->faker->dateTimeBetween($dateLimite, '+2 months');

        return [
            'libelle_concours' => $this->faker->sentence(3),
            'date_limite' => $dateLimite->format('Y-m-d'),
            'date_examen' => $dateExamen->format('Y-m-d'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
