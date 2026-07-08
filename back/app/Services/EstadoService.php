<?php

declare(strict_types=1);

namespace App\Services;

class EstadoService extends SimpleCrudService
{
    protected string $table = 'Estado';

    protected string $primaryKey = 'sigla';

    protected string $primaryKeyInput = 'sigla';

    protected bool $autoIncrement = false;

    protected array $columns = [
        'sigla' => 'sigla',
        'nome' => 'nome',
    ];
}
