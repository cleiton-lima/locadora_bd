<?php

namespace App\Http\Requests\PessoaFisica;

use Illuminate\Foundation\Http\FormRequest;

class StorePessoaFisicaRequest extends FormRequest
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
            'cliente_id' => ['required', 'integer'],
            'cpf' => ['required', 'string', 'size:11'],
            'cnh_numero' => ['required', 'string', 'size:11'],
        ];
    }
}
