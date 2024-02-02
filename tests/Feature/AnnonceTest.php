<?php

namespace Tests\Feature;

use App\Models\Annonce;
use App\Models\Etudiant;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnnonceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function testAjoutAnnonce()
    {
        $this->artisan('migrate:fresh');
        $user = User::factory()->create(['role'=> 'etudiant']);
        $etudiant = Etudiant::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->json('POST', "/api/ajoutAnnonce", [
            'caracteristiques' => 'Appartement',
            'budget' => 100000,
            'description' => 'Description du logement',
            'etudiant_id' => $etudiant->id,
        ]);

        $response->assertStatus(200);
    }

    public function testModificationAnnonce()
    {
        $this->artisan('migrate:fresh');
        $user = User::factory()->create(['role'=> 'etudiant']);
        $etudiant = Etudiant::factory()->create(['user_id' => $user->id]);
        $annonce = Annonce::factory()->create(['etudiant_id' => $etudiant->id]);

        $this->actingAs($user);

        $response = $this->json('PUT', "/api/annonces/{$annonce->id}", [
            'caracteristiques' => 'Appartement',
            'budget' => 100000,
            'description' => 'Description du logement',
            'etudiant_id' => $etudiant->id,
        ]);

        $response->assertStatus(200);
    }

    public function testSuppressionAnnonce()
    {
        $this->artisan('migrate:fresh');
        $user = User::factory()->create(['role'=> 'etudiant']);
        $etudiant = Etudiant::factory()->create(['user_id' => $user->id]);
        $annonce = Annonce::factory()->create(['etudiant_id' => $etudiant->id]);

        $this->actingAs($user);

        $response = $this->json('DELETE', "/api/annonces/{$annonce->id}");

        $response->assertStatus(200);
    }

    public function testMarquerAnnoncePrisEnCharge()
    {
        $this->artisan('migrate:fresh');
        $user = User::factory()->create(['role'=> 'etudiant']);
        $etudiant = Etudiant::factory()->create(['user_id' => $user->id]);
        $annonce = Annonce::factory()->create(['etudiant_id' => $etudiant->id]);

        $this->actingAs($user);

        $response = $this->json('PUT', "/api/marquerPrisEncharge/{$annonce->id}");

        $response->assertStatus(200);
    }

    public function testListeAnnonce()
    {
        $response = $this->get('/api/annonces');
        $response->assertStatus(200);
    }
}
