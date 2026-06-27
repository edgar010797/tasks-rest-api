<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
	public function toArray(Request $request): array
	{
		return [
			'name' => $this->name,
			'firstname' => $this->firstname,
			'lastname' => $this->lastname,
			'email' => $this->email,
			'phone' => $this->phone,
		];
	}
}
