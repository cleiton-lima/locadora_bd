<?php

namespace App\Http\Requests\Lote;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoteRequest extends FormRequest
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
            'gerente_comercial_funcionario_id' => ['required', 'integer'],
            'montadora_id' => ['required', 'integer'],
            'preco_total' => ['required', 'numeric', 'gt:0'],
            'quantidade_veiculos' => ['required', 'integer', 'gt:0'],
        ];
    }
}
