<?php

namespace App\Http\Requests\Aluguel;

use Illuminate\Foundation\Http\FormRequest;

class StoreAluguelRequest extends FormRequest
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
            'atendente_entrega_id' => ['required', 'integer'],
            'atendente_devolucao_id' => ['nullable', 'integer'],
            'pessoa_fisica_id' => ['nullable', 'integer', 'required_without:contrato_frota_id'],
            'contrato_frota_id' => ['nullable', 'integer', 'required_without:pessoa_fisica_id'],
            'status' => ['required', 'string', 'max:30'],
            'valor' => ['nullable', 'numeric', 'gt:0'],
            'tipo' => ['required', 'string', 'max:30'],
            'data_inicial' => ['required', 'date'],
            'data_final' => ['nullable', 'date', 'after_or_equal:data_inicial'],
            'data_final_prevista' => ['required', 'date', 'after_or_equal:data_inicial'],
            'veiculo_id' => ['required', 'integer'],
        ];
    }
}
