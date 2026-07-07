<?php

declare(strict_types=1);

namespace App\Services;

class CidadeService extends SimpleCrudService
{
    protected string $table = 'Cidade';

    protected string $primaryKey = 'id';

    protected string $primaryKeyInput = 'id';

    protected array $columns = [
        'nome' => 'nome',
        'estado_sigla' => 'Estado_sigla',
    ];

    protected array $selectColumns = ['id', 'nome', 'Estado_sigla'];

    public function estadoExiste(string $sigla): bool
    {
        return $this->registroExiste('Estado', 'sigla', $sigla);
    }
}
