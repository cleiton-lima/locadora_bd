<?php

namespace App\Http\Requests\PessoaFisica;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePessoaFisicaRequest extends FormRequest
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
            'cpf' => ['required', 'string', 'size:11'],
            'cnh' => ['required', 'string', 'size:11'],
            'estado' => ['required', 'string', 'size:2'],
            'categoria' => ['required', 'string', 'max:3'],
            'data_emissao' => ['required', 'date'],
            'data_validade' => ['required', 'date', 'after_or_equal:data_emissao'],
        ];
    }
}
