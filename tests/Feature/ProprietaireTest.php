<?php

namespace Tests\Feature;

use App\Models\Proprietaire;
use Tests\TestCase;
use App\Models\User;
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
            'password' => 'passer123',
            'telephone' => '+221781971737',
            'role' => 'proprietaire',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                    "message" => "Propriétaire enregistré avec succés",
                    'proprietaire' => $response->json('proprietaire')
                 ]);
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
            'telephone' => '+221781971737',
            'role' => 'proprietaire',
        ]);

        $response->assertStatus(200);
    }
}
