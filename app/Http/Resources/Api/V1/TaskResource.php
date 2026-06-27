<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date?->format('Y-m-d\TH:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d\TH:i:s'),
            'status' => $this->whenLoaded('status', fn() => [
                'id' => $this->status->id,
                'name' => $this->status->name,
                'slug' => $this->status->slug,
            ]),
            'priority' => $this->whenLoaded('priority', fn() => [
                'id' => $this->priority->id,
                'name' => $this->priority->name,
                'slug' => $this->priority->slug,
                'color' => $this->priority->color,
                'level' => $this->priority->level,
            ]),
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
        ];
    }
}
