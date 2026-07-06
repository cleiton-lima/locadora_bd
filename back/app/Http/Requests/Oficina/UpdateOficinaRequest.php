<?php

namespace App\Http\Requests\Oficina;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOficinaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
        ];
    }
}
