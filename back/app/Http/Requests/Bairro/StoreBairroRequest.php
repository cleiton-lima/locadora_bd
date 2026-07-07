<?php

namespace App\Http\Requests\Bairro;

use Illuminate\Foundation\Http\FormRequest;

class StoreBairroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100'],
            'cidade_id' => ['required', 'integer'],
        ];
    }
}
