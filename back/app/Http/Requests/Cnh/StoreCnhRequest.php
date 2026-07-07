<?php

namespace App\Http\Requests\Cnh;

use Illuminate\Foundation\Http\FormRequest;

class StoreCnhRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'numero' => ['required', 'string', 'size:11'],
            'estado' => ['required', 'string', 'size:2'],
            'categoria' => ['required', 'string', 'max:3'],
            'data_emissao' => ['required', 'date'],
            'data_validade' => ['required', 'date', 'after_or_equal:data_emissao'],
        ];
    }
}
