<?php

namespace App\Http\Requests\Usuario;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
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
            'senha' => ['required', 'string', 'min:6'],
            'ativo' => ['sometimes', 'boolean'],
        ];
    }
}
