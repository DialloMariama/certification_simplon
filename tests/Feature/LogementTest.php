<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Localite;
use App\Models\Logement;
use App\Models\Proprietaire;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LogementTest extends TestCase
{

    /**
     * A basic feature test example.
     */
  
    public function testAjoutLogement()
    {
        $this->artisan('migrate:fresh');
        $admin = User::factory()->create(['role'=> 'admin']);
        $localite = Localite::factory()->create(['user_id' => $admin->id]);
        $user = User::factory()->create(['role'=> 'proprietaire','telephone'=>'+221784067890','email'=>'magid5@gmail.com']);
        $proprietaire = Proprietaire::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->json('POST', "/api/ajoutLogements", [
            'type' => 'Appartement',
            'adresse' => '123 Rue Principale',
            'description' => 'Description du logement',
            'disponibilite' => '2024-03-19 10:52:54',
            'superficie' => 100,
            'prix' => 1200,
            'nombreChambre' => 2,
            'equipements' => 'Internet, Climatisation, Chauffage',
            'localite_id' => $localite->id,
            'image' => [
                UploadedFile::fake()->image('logement1.jpg'),
                UploadedFile::fake()->image('logement2.jpg'),
            ],
            'proprietaire_id' => $proprietaire->id,
        ]);

        $response->assertStatus(200);
    }

    public function testModificationLogement()
    {
        $this->artisan('migrate:fresh');
        $admin = User::factory()->create(['role'=> 'admin']);
        $localite = Localite::factory()->create(['user_id' => $admin->id]);
        $user = User::factory()->create(['role'=> 'proprietaire','telephone'=>'221774067890','email'=>'magid5@gmail.com']);
        $proprietaire = Proprietaire::factory()->create(['user_id' => $user->id]);

        $logement = Logement::factory()->create(['proprietaire_id' => $proprietaire->id]);

        $this->actingAs($user);

        $response = $this->json('POST', "/api/logements/{$logement->id}", [
            'type' => 'Maison',
            'adresse' => '456 Rue Secondaire',
            'description' => 'Nouvelle description du logement',
            'disponibilite' => '2024-04-19 12:30:00',
            'superficie' => 150,
            'prix' => 1500,
            'nombreChambre' => 3,
            'equipements' => 'Internet, Climatisation, Chauffage',
            'localite_id' => $localite->id,
        ]);

        $response->assertStatus(200);
    }

    public function testSuppressionLogement()
    {
        $this->artisan('migrate:fresh');
        $admin = User::factory()->create(['role'=> 'admin']);
        $localite = Localite::factory()->create(['user_id' => $admin->id]);
        $user = User::factory()->create(['role'=> 'proprietaire','telephone'=>'+221774067890','email'=>'magid5@gmail.com']);
        $proprietaire = Proprietaire::factory()->create(['user_id' => $user->id]);

        $logement = Logement::factory()->create(['proprietaire_id' => $proprietaire->id]);

        $this->actingAs($user);

        $response = $this->json('DELETE', "/api/logements/{$logement->id}");

        $response->assertStatus(200);
    }

    public function testListeLogements()
    {
        $this->artisan('migrate:fresh');
        // $user = User::factory()->create(['role'=> 'proprietaire']);
        // $proprietaire = Proprietaire::factory()->create(['user_id' => $user->id]);
        // $logement = Logement::factory()->create(['proprietaire_id' => $proprietaire->id]);

        // $this->actingAs($user);

        $response = $this->json('GET', '/api/logements');

        $response->assertStatus(200);
    }
}
