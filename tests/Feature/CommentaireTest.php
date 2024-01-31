<?php

namespace Tests\Feature;

use App\Models\Commentaire;
use Tests\TestCase;
use App\Models\User;
use App\Models\Etudiant;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentaireTest extends TestCase
{
    /**
     * A basic feature test example.
     */


    public function testFaireCommentaire()
    {
        $user = User::factory()->create();
        $etudiant = Etudiant::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->json('POST', '/api/ajoutCommentaire', [
            'texte' => 'joli appartement',
            'logement_id' => 8,
            'etudiant_id' => $etudiant->id,
        ]);
        $response->assertStatus(200);
    }

    public function testModificationCommentaire()
    {
        $user = User::factory()->create();
        $etudiant = Etudiant::factory()->create(['user_id' => $user->id]);
        $commentaire = Commentaire::factory()->create(['etudiant_id' => $etudiant->id]);

        $this->actingAs($user);

        $response = $this->json('PUT', "/api/commentaires/{$commentaire->id}", [
            'texte' => 'joli studio',
            'logement_id' => 8,
            'etudiant_id' => $etudiant->id,
        ]);

        $response->assertStatus(200);

    }

    public function testSuppressionCommentaire()
    {
        $user = User::factory()->create();
        $etudiant = Etudiant::factory()->create(['user_id' => $user->id]);
        $commentaire = Commentaire::factory()->create(['etudiant_id' => $etudiant->id]);

        $this->actingAs($user);

        $response = $this->json('DELETE', "/api/commentaires/{$commentaire->id}");

        $response->assertStatus(200);
    }

}
