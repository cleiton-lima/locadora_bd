<?php

namespace App\Http\Requests\Venda;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendaRequest extends FormRequest
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
            'valor' => ['required', 'numeric', 'gt:0'],
            'status' => ['required', 'string', 'max:30'],
            'veiculo_id' => ['required', 'integer'],
            'pessoa_fisica_id' => ['required', 'integer'],
            'gerente_comercial_funcionario_id' => ['required', 'integer'],
        ];
    }
}
