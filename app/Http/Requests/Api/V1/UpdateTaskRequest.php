<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => 'sometimes|required|string|max:255',
            'description'   => 'nullable|string',
            'due_date'      => 'sometimes|required|date_format:Y-m-d\TH:i:s',
            'status_id'     => 'sometimes|required|integer|exists:statuses,id',
            'priority_id'   => 'sometimes|required|integer|exists:priorities,id',
            'category_id'   => 'sometimes|required|integer|exists:categories,id',
        ];
    }
}
