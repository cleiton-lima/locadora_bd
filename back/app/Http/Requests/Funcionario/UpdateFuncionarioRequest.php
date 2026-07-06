<?php

namespace App\Http\Requests\Funcionario;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFuncionarioRequest extends FormRequest
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
            'nome' => ['required', 'string', 'max:30'],
            'sobrenome' => ['required', 'string', 'max:30'],
            'telefone' => ['required', 'string', 'size:11'],
            'filial_id' => ['required', 'integer'],
        ];
    }
}
