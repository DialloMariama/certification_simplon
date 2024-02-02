<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnonceRessource extends JsonResource
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
            'etudiantpaysOrigine' => $this->etudiant->paysOrigine,
            'etudiantUniversite' => $this->etudiant->universite,
        ];
    }
}
