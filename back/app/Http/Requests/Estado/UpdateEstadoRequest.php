<?php

namespace App\Http\Requests\Estado;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:100'],
        ];
    }
}
