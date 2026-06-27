<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'due_date'      => 'required|date_format:Y-m-d\TH:i:s',
            'status_id'     => 'required|integer|exists:statuses,id',
            'priority_id'   => 'required|integer|exists:priorities,id',
            'category_id'   => 'required|integer|exists:categories,id',
        ];
    }
}
