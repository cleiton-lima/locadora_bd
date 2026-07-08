<?php

namespace App\Http\Requests\Veiculo;

use Illuminate\Foundation\Http\FormRequest;

class StoreVeiculoRequest extends FormRequest
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
            'finalidade' => ['required', 'string', 'max:30'],
            'placa' => ['required', 'string', 'size:7'],
            'grupo' => ['required', 'string', 'size:1'],
            'quilometragem' => ['required', 'integer', 'min:0'],
            'administrador_cadastro_id' => ['required', 'integer'],
            'administrador_responsavel_id' => ['nullable', 'integer'],
            'filial_id' => ['required', 'integer'],
            'lote_id' => ['required', 'integer'],
        ];
    }
}
