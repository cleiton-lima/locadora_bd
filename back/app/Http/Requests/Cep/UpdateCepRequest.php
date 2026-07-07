<?php

namespace App\Http\Requests\Cep;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'logradouro_id' => ['required', 'integer'],
        ];
    }
}
