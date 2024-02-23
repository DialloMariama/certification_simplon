<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Etudiant;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EtudiantTest extends TestCase
{
    /**
     * A basic feature test example.
     */
   

    public function testInscriptionEtudiant()
    {
        $this->artisan('migrate:fresh');
        $response = $this->json('POST', '/api/inscriptionEtudiant', [
            'nom' => 'BAH',
            'prenom' => 'Magid',
            'adresse' => 'Médina Rue x 25',
            'email' => 'maridiallo@gmail.com',
            'password' => 'password',
            'telephone' => '221781971737',
            'paysOrigine' => 'Sénégal',
            'universite' => 'UCAD',
            'role' => 'etudiant',
            'inscriptionValidee' => 'valider',
            'papierJustificatif' => UploadedFile::fake()->create('papier_justificatif.pdf', 1000),
        ]);

        $response->assertStatus(201);
    }

    public function testModificationEtudiant()
    {
        $this->artisan('migrate:fresh');
        $user = User::factory()->create(['role'=> 'etudiant']);
        $etudiant = Etudiant::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->json('PUT', '/api/updateEtudiant', [
            'nom' => 'BAH',
            'prenom' => 'Magid',
            'adresse' => 'Médina Rue x 25',
            'email' => 'maridiallo@gmail.com',
            'password' => 'password',
            'telephone' => '221781971737',
            'paysOrigine' => 'Sénégal',
            'universite' => 'UCAD',
            'role' => 'etudiant',
            'inscriptionValidee' => 'valider',
            'papierJustificatif' => UploadedFile::fake()->create('papier_justificatif.pdf', 1000),
        ]);

        $response->assertStatus(200);
    }
}
