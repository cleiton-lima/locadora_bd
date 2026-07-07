<?php

namespace App\Http\Requests\Cnh;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCnhRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', 'string', 'size:2'],
            'categoria' => ['required', 'string', 'max:3'],
            'data_emissao' => ['required', 'date'],
            'data_validade' => ['required', 'date', 'after_or_equal:data_emissao'],
        ];
    }
}
