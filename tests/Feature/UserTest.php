<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    public function testUserLogin()
    {
        $password = 'passer123';
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make($password),
        ]);
        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);
        $response->assertStatus(200);
    }
    public function testInscriptionLogin()
    {

        $password = 'passer123';
        $user = User::factory()->create([
            'role' => 'admin',
            'password' => Hash::make($password),
        ]);
        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);
        $response->assertStatus(200);
    }


    public function testEmailIsUnique()
    {
        $existingUser = User::factory()->create();

        $response = $this->Post('/api/inscriptionEtudiant', [
            'nom' => 'John',
            'prenom' => 'Doe',
            'telephone' => '123456789',
            'adresse' => '123 Main Street',
            'email' => $existingUser->email,
            'password' => 'password123',
            'role' => 'etudiant',
        ]);
        $response->assertStatus(422);
    }

    public function test_Deconnexion()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->post('/api/logout');
        $response->assertStatus(200);
    }
}
