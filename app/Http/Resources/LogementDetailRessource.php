<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogementDetailRessource extends JsonResource
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
            'type' => $this->type,
            'disponibilite' => $this->disponibilite,
            'description' => $this->description,
            'superficie' => $this->superficie,
            'prix' => $this->prix,
            'nombreChambre' => $this->nombreChambre,
            'equipements' => $this->equipements,
            'localite' => $this->localite ? $this->localite->nomLocalite : null,
            'localiteCommune' => $this->localite ? $this->localite->commune : null,
            'adresse' => $this->adresse,
            'proprietaire' => $this->proprietaire->user->nom,
            'proprietairePrenom' => $this->proprietaire->user->prenom,
            'proprietaireTelephone' => $this->proprietaire->user->telephone,
            'proprietaireEmail' => $this->proprietaire->user->email,
            'image' => $this->images,
            'commentaire' => CommentaireRessource::collection($this->commentaires) ,
            
        ];
    }
}
