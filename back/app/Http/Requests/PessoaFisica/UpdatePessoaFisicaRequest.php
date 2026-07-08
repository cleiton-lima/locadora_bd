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
            'cnh_numero' => ['required', 'string', 'size:11'],
        ];
    }
}
