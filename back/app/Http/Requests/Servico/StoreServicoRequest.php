<?php

namespace App\Http\Requests\Servico;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicoRequest extends FormRequest
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
            'status' => ['required', 'string', 'max:30'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'custo' => ['nullable', 'numeric', 'min:0'],
            'tipo' => ['required', 'string', 'max:30'],
            'veiculo_id' => ['required', 'integer'],
            'administrador_funcionario_id' => ['required', 'integer'],
            'oficina_id' => ['required', 'integer'],
        ];
    }
}
