<?php

declare(strict_types=1);

namespace App\Services;

class CnhService extends SimpleCrudService
{
    protected string $table = 'CNH';

    protected string $primaryKey = 'numero';

    protected string $primaryKeyInput = 'numero';

    protected bool $autoIncrement = false;

    protected array $columns = [
        'numero' => 'numero',
        'estado' => 'estado',
        'categoria' => 'categoria',
        'data_emissao' => 'data_emissao',
        'data_validade' => 'data_validade',
    ];

    public function estadoExiste(string $sigla): bool
    {
        return $this->registroExiste('Estado', 'sigla', $sigla);
    }
}
