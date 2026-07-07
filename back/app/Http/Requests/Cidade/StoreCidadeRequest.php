<?php

namespace App\Http\Requests\Cidade;

use Illuminate\Foundation\Http\FormRequest;

class StoreCidadeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100'],
            'estado_sigla' => ['required', 'string', 'size:2'],
        ];
    }
}
