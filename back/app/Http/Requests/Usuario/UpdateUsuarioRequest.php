<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:60'],
            'nome' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:254'],
            'senha' => ['nullable', 'string', 'min:6'],
            'ativo' => ['sometimes', 'boolean'],
        ];
    }
}
