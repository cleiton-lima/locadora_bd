<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CotacaoRequest extends FormRequest
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
        ];
    }
}
