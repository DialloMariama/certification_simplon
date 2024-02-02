<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Etudiant;
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
            'email' => 'magid1@gmail.com',
            'password' => 'passer123',
            'telephone' => '+221781971737',
            'paysOrigine' => 'Sénégal',
            'universite' => 'UCAD',
            'role' => 'etudiant',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Etudiant enregistré avec succés', 
                     'etudiant' => $response->json('etudiant')
                 ]);
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
            'telephone' => '+221781971737',
            'paysOrigine' => 'Sénégal',
            'universite' => 'UCAD',
            'role' => 'etudiant',
        ]);

        $response->assertStatus(200);
    }
}
