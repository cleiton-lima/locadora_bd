<?php

declare(strict_types=1);

namespace App\Services;

class BairroService extends SimpleCrudService
{
    protected string $table = 'Bairro';

    protected string $primaryKey = 'id';

    protected string $primaryKeyInput = 'id';

    protected array $columns = [
        'nome' => 'nome',
        'cidade_id' => 'Cidade_id',
    ];

    protected array $selectColumns = ['id', 'nome', 'Cidade_id'];

    public function cidadeExiste(int $id): bool
    {
        return $this->registroExiste('Cidade', 'id', $id);
    }
}
