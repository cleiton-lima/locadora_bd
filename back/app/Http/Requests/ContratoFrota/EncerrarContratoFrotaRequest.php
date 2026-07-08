<?php

namespace App\Http\Requests\ContratoFrota;

use Illuminate\Foundation\Http\FormRequest;

class EncerrarContratoFrotaRequest extends FormRequest
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
            'data_final' => ['required', 'date'],
        ];
    }
}
