<?php

namespace Database\Factories;

use App\Models\Ecole;
use Illuminate\Database\Eloquent\Factories\Factory;

class EcoleFactory extends Factory
{
    protected $model = Ecole::class;

    public function definition(): array
    {
        return [
            'libelle_ecole'     => $this->faker->company . ' University',
            'code_ecole'        => strtoupper($this->faker->unique()->lexify('ECO??')),
            'localisation'      => $this->faker->city,
            'email_ecole'       => $this->faker->unique()->safeEmail,
            'bp_ecole'          => 'BP ' . $this->faker->numberBetween(100, 999),
            'siteweb_ecole'     => $this->faker->url,
            'telephone_ecole'   => $this->faker->numerify('6########'),
            'logo_url'          => null,
            'banniere_url'      => null,
            'embleme_ecole'     => null,
        ];
    }
}
