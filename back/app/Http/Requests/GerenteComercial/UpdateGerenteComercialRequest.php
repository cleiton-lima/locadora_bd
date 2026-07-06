<?php

namespace App\Http\Requests\GerenteComercial;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGerenteComercialRequest extends FormRequest
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
            'funcionario_id' => ['required', 'integer'],
        ];
    }
}
