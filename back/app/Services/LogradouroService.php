<?php

declare(strict_types=1);

namespace App\Services;

class LogradouroService extends SimpleCrudService
{
    protected string $table = 'Logradouro';

    protected string $primaryKey = 'id';

    protected string $primaryKeyInput = 'id';

    protected array $columns = [
        'nome' => 'nome',
        'bairro_id' => 'Bairro_id',
    ];

    protected array $selectColumns = ['id', 'nome', 'Bairro_id'];

    public function bairroExiste(int $id): bool
    {
        return $this->registroExiste('Bairro', 'id', $id);
    }
}
