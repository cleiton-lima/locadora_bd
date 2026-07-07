<?php

namespace App\Http\Requests\Estado;

use Illuminate\Foundation\Http\FormRequest;

class StoreEstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sigla' => ['required', 'string', 'size:2'],
            'nome' => ['required', 'string', 'max:100'],
        ];
    }
}
