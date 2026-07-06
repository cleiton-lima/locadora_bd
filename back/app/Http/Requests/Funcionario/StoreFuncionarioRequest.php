<?php

namespace App\Http\Requests\Funcionario;

use Illuminate\Foundation\Http\FormRequest;

class StoreFuncionarioRequest extends FormRequest
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
            'usuario_id' => ['required', 'integer'],
            'nome' => ['required', 'string', 'max:30'],
            'sobrenome' => ['required', 'string', 'max:30'],
            'telefone' => ['required', 'string', 'size:11'],
            'filial_id' => ['required', 'integer'],
        ];
    }
}
