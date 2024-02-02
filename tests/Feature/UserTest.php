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
        $this->artisan('migrate:fresh');
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
        $this->artisan('migrate:fresh');

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
        $this->artisan('migrate:fresh');
        $existingUser = User::factory()->create(['role'=>'proprietaire']);

        $response = $this->Post('/api/inscriptionProprietaire', [
            'nom' => 'John',
            'prenom' => 'Doe',
            'telephone' => '+221786574323',
            'adresse' => '123 Main Street',
            'email' => $existingUser->email,
            'password' => 'password123',
            'role' => 'proprietaire',
        ]);
        $response->assertStatus(422);
    }

    public function test_Deconnexion()
    {
        $this->artisan('migrate:fresh');
        $user = User::factory()->create();
        $this->actingAs($user);
        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->post('/api/logout');
        $response->assertStatus(200);
    }
}
