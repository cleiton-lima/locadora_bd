<?php

namespace App\Http\Requests\Empresa;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmpresaRequest extends FormRequest
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
            'cnpj' => ['required', 'string', 'size:14'],
        ];
    }
}
