<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserConsentResource extends JsonResource
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
            'user_id' => $this->user_id,
            'consent_type_id' => $this->consent_type_id,
            'granted' => $this->granted,
            'consent_type' => ConsentTypeResource::make($this->whenLoaded('consentType')),
        ];
    }
}
