<?php

namespace App\Http\Requests\Atendente;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAtendenteRequest extends FormRequest
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
            'funcionario_id' => ['required', 'integer'],
        ];
    }
}
