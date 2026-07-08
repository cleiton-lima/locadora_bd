<?php

namespace App\Http\Requests\Logradouro;

use Illuminate\Foundation\Http\FormRequest;

class StoreLogradouroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:150'],
            'bairro_id' => ['required', 'integer'],
        ];
    }
}
