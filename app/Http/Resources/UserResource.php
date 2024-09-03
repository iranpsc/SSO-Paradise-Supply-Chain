<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->when($this->personalInfo->is_verified, $this->full_name, $this->name),
            'code' => $this->code,
            'avatar' => $this->getFirstMediaUrl('avatars'),
        ];
    }
}
