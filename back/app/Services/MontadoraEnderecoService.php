<?php

declare(strict_types=1);

namespace App\Services;

class MontadoraEnderecoService extends SimpleCrudService
{
    protected string $table = 'Montadora_endereco';

    protected string $primaryKey = 'id';

    protected string $primaryKeyInput = 'id';

    protected array $columns = [
        'montadora_id' => 'Montadora_id',
        'numero' => 'numero',
        'complemento' => 'complemento',
        'referencia' => 'referencia',
        'cep' => 'CEP',
    ];

    protected array $selectColumns = ['id', 'Montadora_id', 'numero', 'complemento', 'referencia', 'CEP'];

    public function montadoraExiste(int $id): bool
    {
        return $this->registroExiste('Montadora', 'id', $id);
    }

    public function cepExiste(string $cep): bool
    {
        return $this->registroExiste('CEP', 'CEP', $cep);
    }
}
