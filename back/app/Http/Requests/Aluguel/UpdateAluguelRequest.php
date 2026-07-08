<?php

namespace App\Http\Requests\Aluguel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAluguelRequest extends FormRequest
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
            'valor' => ['required', 'numeric', 'gt:0'],
            'tipo' => ['required', 'string', 'max:30'],
            'data_inicial' => ['required', 'date'],
            'data_final' => ['nullable', 'date', 'after_or_equal:data_inicial'],
            'data_final_prevista' => ['required', 'date', 'after_or_equal:data_inicial'],
            'veiculo_id' => ['required', 'integer'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $pessoaFisica = $this->filled('pessoa_fisica_id');
                $contratoFrota = $this->filled('contrato_frota_id');

                if ($pessoaFisica === $contratoFrota) {
                    $validator->errors()->add('pessoa_fisica_id', 'Informe pessoa física ou contrato de frota, mas não ambos.');
                    $validator->errors()->add('contrato_frota_id', 'Informe pessoa física ou contrato de frota, mas não ambos.');
                    return;
                }

                if ($pessoaFisica && $this->input('tipo') !== 'CURTA_DURACAO') {
                    $validator->errors()->add('tipo', 'Aluguel de pessoa física deve ser CURTA_DURACAO.');
                }

                if ($contratoFrota && $this->input('tipo') !== 'LONGA_DURACAO') {
                    $validator->errors()->add('tipo', 'Aluguel de frota deve ser LONGA_DURACAO.');
                }
            },
        ];
    }
}
