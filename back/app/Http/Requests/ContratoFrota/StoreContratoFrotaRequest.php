<?php

namespace App\Http\Requests\ContratoFrota;

use Illuminate\Foundation\Http\FormRequest;

class StoreContratoFrotaRequest extends FormRequest
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
            'gerente_comercial_id' => ['required', 'integer'],
            'empresa_id' => ['required', 'integer'],
            'data_inicio' => ['required', 'date'],
            'data_final' => ['required', 'date', 'after_or_equal:data_inicio'],
            'quantidade_veiculos' => ['required', 'integer', 'gt:0'],
        ];
    }
}
