<?php

namespace App\Http\Requests\Cep;

use Illuminate\Foundation\Http\FormRequest;

class StoreCepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cep' => ['required', 'string', 'size:8'],
            'logradouro_id' => ['required', 'integer'],
        ];
    }
}
