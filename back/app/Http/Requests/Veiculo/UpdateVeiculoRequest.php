<?php

namespace App\Http\Requests\Veiculo;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVeiculoRequest extends FormRequest
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
            'administrador_funcionario_id' => ['required', 'integer'],
            'filial_id' => ['required', 'integer'],
            'lote_id' => ['required', 'integer'],
        ];
    }
}
