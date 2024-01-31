<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Logement>
 */
class LogementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'Appartement',
            'adresse' => 'médina',
            'description' => '2 chambres',
            'disponibilite' => '2024-03-19 12:30:00',
            'superficie' =>  200,
            'prix' => 3000,
            'nombreChambre' =>  5,
            'equipements' => 'climatisation',
            'localite_id' => 2,
            'proprietaire_id' => function () {
                
                return factory(\App\Models\Proprietaire::class)->create()->id;
            },
        ];
    }
}
