<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnonceDetailRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'budget' => $this->budget,
            'caracteristiques' => $this->caracteristiques,
            'prisEnCharge' => $this->prisEnCharge,
            'etudiant' => $this->etudiant->user->nom,
            'etudiantPrenom' => $this->etudiant->user->prenom,
            'etudiantEmail' => $this->etudiant->user->email,
            'etudiantAdresse' => $this->etudiant->user->adresse,
            'etudiantTelephone' => $this->etudiant->user->telephone,
            'etudiantpaysOrigine' => $this->etudiant->paysOrigine,
            'etudiantUniversite' => $this->etudiant->universite,
        ];
    }
}
