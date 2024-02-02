<?php

namespace Tests\Feature;

use App\Models\Commentaire;
use Tests\TestCase;
use App\Models\User;
use App\Models\Etudiant;
use App\Models\Localite;
use App\Models\Logement;
use App\Models\Proprietaire;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CommentaireTest extends TestCase
{
    /**
     * A basic feature test example.
     */


    public function testFaireCommentaire()
    {
        $this->artisan('migrate:fresh');
        $admin = User::factory()->create(['role'=> 'admin']);
        $localite = Localite::factory()->create(['user_id' => $admin->id]);

        $user1 = User::factory()->create(['role'=> 'proprietaire','telephone'=>'+221774567890','email'=>'magid@gmail.com']);
        $proprietaire = Proprietaire::factory()->create(['user_id' => $user1->id]);
        $logement = Logement::factory()->create(['proprietaire_id'=> $proprietaire->id]);


        $user = User::factory()->create(['role'=> 'etudiant','telephone'=>'+221774567690','email'=>'magid2@gmail.com']);
        $etudiant = Etudiant::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);
        $response = $this->json('POST', '/api/ajoutCommentaire', [
            'texte' => 'joli appartement',
            'logement_id' => $logement->id,
            'etudiant_id' => $etudiant->id,
        ]);
        $response->assertStatus(200);
    }

    public function testModificationCommentaire()
    {
        $this->artisan('migrate:fresh');
        $admin = User::factory()->create(['role'=> 'admin']);
        $localite = Localite::factory()->create(['user_id' => $admin->id]);

        $user1 = User::factory()->create(['role'=> 'proprietaire','telephone'=>'+221774567890','email'=>'magid@gmail.com']);
        $proprietaire = Proprietaire::factory()->create(['user_id' => $user1->id]);
        $logement = Logement::factory()->create(['proprietaire_id'=> $proprietaire->id]);


        $user = User::factory()->create(['role'=> 'etudiant','telephone'=>'+221774567690','email'=>'magid2@gmail.com']);
        $etudiant = Etudiant::factory()->create(['user_id' => $user->id]);
        $commentaire = Commentaire::factory()->create(['etudiant_id' => $etudiant->id]);


        $this->actingAs($user);

        $response = $this->json('PUT', "/api/commentaires/{$commentaire->id}", [
            'texte' => 'joli studio',
            'logement_id' => $logement->id,
            'etudiant_id' => $etudiant->id,
        ]);

        $response->assertStatus(200);

    }

    public function testSuppressionCommentaire()
    {
        $this->artisan('migrate:fresh');
        $admin = User::factory()->create(['role'=> 'admin']);
        $localite = Localite::factory()->create(['user_id' => $admin->id]);

        $user1 = User::factory()->create(['role'=> 'proprietaire','telephone'=>'+221774567890','email'=>'magid@gmail.com']);
        $proprietaire = Proprietaire::factory()->create(['user_id' => $user1->id]);
        $logement = Logement::factory()->create(['proprietaire_id'=> $proprietaire->id]);


        $user = User::factory()->create(['role'=> 'etudiant','telephone'=>'+221774567690','email'=>'magid2@gmail.com']);
        $etudiant = Etudiant::factory()->create(['user_id' => $user->id]);
        $commentaire = Commentaire::factory()->create(['etudiant_id' => $etudiant->id]);

        $this->actingAs($user);

        $response = $this->json('DELETE', "/api/commentaires/{$commentaire->id}");

        $response->assertStatus(200);
    }

}
