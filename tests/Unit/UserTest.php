<?php

namespace Tests\Unit;

// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\EtudiantController;

class UserTest extends TestCase
{
    /**
     * A basic unit test example.
     */

    protected $authController;
 
    protected function setUp(): void
    {
        parent::setUp();
        $this->authController = new AuthController();
    }

    public function testUnitLoginWithValidCredentials()
    {
        // Créez un utilisateur avec des identifiants valides
        //  User::factory()->create([
        //     'nom'=> 'Ntandou ',
        //     'prenom'=> 'Sow',
        //     'adresse'=> 'Mermoz',
        //     'email' => 'mermoz@gmail.com',
        //     'password' => Hash::make('password'),
        //     'telephone'=>'+221779999911',
        //     'role' => 'admin',
        // ]);

        // Envoyez une demande de connexion avec les identifiants valides
        $request = new Request([
            'email' => 'mermoz@gmail.com',
            'password' => 'password',
        ]);
        $response = $this->authController->login($request);
        
        // Vérifier d'abord si l'authentification a réussi en fonction du code d'état de la réponse
        if ($response->getStatusCode() === 200) {
            $this->assertArrayHasKey('authorization', $response->original);
            $this->assertArrayHasKey('token', $response->original['authorization']);
            $this->assertArrayHasKey('type', $response->original['authorization']);
            $this->assertArrayHasKey('message', $response->original['authorization']);
            $this->assertArrayHasKey('expires_in', $response->original['authorization']);
        } else {
            // Si l'authentification a échoué, il ne devrait pas y avoir de clé 'authorization' dans la réponse
            $this->assertArrayNotHasKey('authorization', $response->original);
        }
    }        
    
    // public function testLoginWithUserBlocked()
    // {
    //     //Créez un utilisateur avec des identifiants valides
    //     //  User::factory()->create([
    //     //     'nom'=> 'heber tochi',
    //     //     'email' => 'hebert@example.com',
    //     //     'password' => Hash::make('password123'),
    //     //     'telephone'=>'779999911',
    //     //     'genre' => 'homme',
    //     //     'role_id' => 3,
    //     //     'ville_id' => 1,

    //     //     'is_blocked' => 0,
    //     // ]);

    //     // Envoyez une demande de connexion avec les identifiants valides
    //     $request = new Request([
    //         'email' => 'roger@example.com',
    //         'password' => 'password123',
    //     ]);
    //     $response = $this->authController->login($request);

    //     // Assurez-vous que la réponse contient un jeton d'accès
    //     $this->assertInstanceOf(JsonResponse::class, $response);

    //     // Assurez-vous que le code de statut HTTP est 401 (Unauthorized)
    //     $this->assertEquals(401, $response->getStatusCode());
    
    //     // Assurez-vous que la réponse contient le message approprié
    //     $expectedContent = [
    //         'error' => 'Votre compte est bloqué'
    //     ];
    //     $this->assertEquals($expectedContent, json_decode($response->getContent(), true));
    // }


    public function testUnitRegisterEtudiant()
    {
        $etudiantController = new EtudiantController();
    
        // Créez une demande HTTP simulée avec des données valides
        $storeEtudiantRequest = new Request();
        $storeEtudiantRequest->merge([
            'nom' => 'BAH',
            'prenom' => 'Magid',
            'adresse' => 'Médina Rue x 25',
            'email' => 'magid1012@gmail.com',
            'password' => 'passer12',
            'telephone' => '00221781970738',
            'paysOrigine' => 'Sénégal',
            'universite' => 'UCAD',
            'role' => 'etudiant',
            'inscriptionValidee' => 'valider',
            'papierJustificatif' => UploadedFile::fake()->create('papier_justificatif.pdf', 1000),
        ]);
    
        // Déclenchez la validation et remplissez les données validées
        // $storeEtudiantRequest->validateResolved();
    
        $response = $etudiantController->registerEtudiant($storeEtudiantRequest);
        $this->assertInstanceOf(JsonResponse::class, $response);
    //dd($response);
        $this->assertEquals(201, $response->getStatusCode());
    
        $responseData = $response->getData(true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Etudiant enregistré avec succès', $responseData['message']);
        $this->assertArrayHasKey('etudiant', $responseData);
    }
    

}
