<?php

namespace Tests\Feature;

use App\Models\Localite;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LocaliteTest extends TestCase
{
    /**
     * A basic feature test example.
     */
   
     public function testAjoutLocalite()
     {
        $this->artisan('migrate:fresh');
         $user = User::factory()->create(['role'=>'admin']);
 
         $this->actingAs($user);
 
         $response = $this->json('POST', "/api/ajoutLocalites", [
             'nomLocalite' => 'Keurmassar',
             'commune' => 'Rufisque',
         ]);
 
         $response->assertStatus(200);
     }
 

     public function testModificationLocalite()
     {
        $this->artisan('migrate:fresh');
         $user = User::factory()->create(['role'=>'admin']);
        $localite = Localite::factory()->create(['user_id' => $user->id]);

 
         $this->actingAs($user);
 
         $response = $this->json('PUT', "/api/localites/{$localite->id}", [
            'nomLocalite' => 'Keurmassar',
            'commune' => 'Rufisque',
        ]);
 
         $response->assertStatus(200);
     }
 
     public function testSuppressionLocalite()
     {
        $this->artisan('migrate:fresh');
         $user = User::factory()->create(['role'=>'admin']);
         $localite = Localite::factory()->create(['user_id' => $user->id]);
 
         $this->actingAs($user);
 
         $response = $this->json('DELETE', "/api/localites/{$localite->id}");
 
         $response->assertStatus(200);
     }
 
     public function testListeLocalite()
     {
        $this->artisan('migrate:fresh');
        $user = User::factory()->create(['role'=>'admin']);

        $this->actingAs($user);
         $response = $this->get('/api/localites');
         $response->assertStatus(200);
     }
}
