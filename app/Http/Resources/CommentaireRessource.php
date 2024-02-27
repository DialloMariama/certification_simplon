<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentaireRessource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'texte' => $this->texte,
            'etudiant' => $this->etudiant->user->nom,
            'etudiantPrenom' => $this->etudiant->user->prenom,
        ];
    }
}
