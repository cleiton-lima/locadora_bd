<?php

namespace App\Http\Requests\Aluguel;

use Illuminate\Foundation\Http\FormRequest;

class DevolverAluguelRequest extends FormRequest
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
            'atendente_devolucao_id' => ['required', 'integer'],
            'data_final' => ['required', 'date'],
        ];
    }
}
