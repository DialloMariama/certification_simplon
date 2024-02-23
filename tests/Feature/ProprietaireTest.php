<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Proprietaire;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProprietaireTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testInscriptionProprietaire()
    {
        $this->artisan('migrate:fresh');
        $response = $this->json('POST', '/api/inscriptionProprietaire', [
            'nom' => 'BAH',
            'prenom' => 'Magid',
            'adresse' => 'Médina Rue x 25',
            'email' => 'magid123@gmail.com',
            'password' => 'passer12',
            'telephone' => '00221781971737',
            'role' => 'proprietaire',
            'inscriptionValidee' => 'valider',
            'papierJustificatif' => UploadedFile::fake()->create('papier_justificatif.pdf', 1000),

        ]);

        $response->assertStatus(200);
    }

    public function testModificationProprietaire()
    {
        $this->artisan('migrate:fresh');
        $user = User::factory()->create(['role'=> 'proprietaire']);
        $proprietaire = Proprietaire::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->json('PUT', '/api/updateProprietaire', [
            'nom' => 'BAH',
            'prenom' => 'Magid',
            'adresse' => 'Médina Rue x 25',
            'email' => 'maridiallo@gmail.com',
            'password' => 'password',
            'telephone' => '221781971737',
            'role' => 'proprietaire',
            'inscriptionValidee' => 'valider',
            'papierJustificatif' => UploadedFile::fake()->create('papier_justificatif.pdf', 1000),
        ]);

        $response->assertStatus(200);
    }
}
