<?php

declare(strict_types=1);

namespace App\Services;

class CepService extends SimpleCrudService
{
    protected string $table = 'CEP';

    protected string $primaryKey = 'CEP';

    protected string $primaryKeyInput = 'cep';

    protected bool $autoIncrement = false;

    protected array $columns = [
        'cep' => 'CEP',
        'logradouro_id' => 'Logradouro_id',
    ];

    public function logradouroExiste(int $id): bool
    {
        return $this->registroExiste('Logradouro', 'id', $id);
    }
}
