<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 */
	public function authorize(): bool
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
	 */
	public function rules(): array
	{
		return [
			'name'  => ['sometimes', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
			'password' => ['required', 'min:5'],
			'firstname'  => ['sometimes', 'string', 'max:255'],
			'lastname'  => ['sometimes', 'string', 'max:255'],
			'phone'     => ['sometimes', 'string', 'min:7', 'max:20', 'regex:/^[+]?[0-9\s\-\(\)]+$/'],
		];
	}
}
