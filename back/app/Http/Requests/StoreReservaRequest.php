<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReservaRequest extends FormRequest
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
            'filial_retirada_id' => ['required', 'integer'],
            'filial_devolucao_id' => ['required', 'integer'],
            'data_retirada' => ['required', 'date'],
            'data_devolucao' => ['required', 'date', 'after:data_retirada'],
            'grupo' => ['required', 'string', 'max:1'],
            'quilometragem_tipo' => ['required', Rule::in(['ECONOMICA', 'ILIMITADA'])],
            'adicionais' => ['nullable', 'array'],
            'adicionais.*' => ['string', Rule::in(['LAVAGEM_GARANTIDA', 'PROTECAO_COMPLETA', 'CONDUTOR_ADICIONAL'])],
            'total_informado' => ['nullable', 'numeric', 'gt:0'],
        ];
    }
}
