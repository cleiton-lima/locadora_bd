<?php

namespace App\Http\Requests\UsuarioEndereco;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioEnderecoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'usuario_id' => ['required', 'integer'],
            'numero' => ['nullable', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:100'],
            'referencia' => ['nullable', 'string', 'max:100'],
            'cep' => ['required', 'string', 'size:8'],
            'estado' => ['prohibited'],
            'cidade' => ['prohibited'],
            'bairro' => ['prohibited'],
            'logradouro' => ['prohibited'],
        ];
    }
}
