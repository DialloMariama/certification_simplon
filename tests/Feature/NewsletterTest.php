<?php

namespace Tests\Feature;

use App\Models\Newsletter;
use Database\Factories\NewsletterFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    /**
     * A basic feature test example.
     */
 
    // use RefreshDatabase; // Utilisé pour réinitialiser la base de données après chaque test

    /** @test */
    public function testCreerNewsletter()
    {
        $requestData = [
            'email' => 'test@example.com',
        ];

        $response = $this->json('POST', '/api/newsletter', $requestData);

        $response->assertStatus(200);

        
    }

    /** @test */
    public function TestDuplicationEmail()
    {
       
        $existingEmail = Newsletter::factory()->create();

        $response = $this->json('POST', '/api/newsletter',[
            'email' => $existingEmail,
            ]);

        $response->assertStatus(422);

        
    }


}
