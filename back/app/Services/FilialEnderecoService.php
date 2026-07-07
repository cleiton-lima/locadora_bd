<?php

declare(strict_types=1);

namespace App\Services;

class FilialEnderecoService extends SimpleCrudService
{
    protected string $table = 'Filial_endereco';

    protected string $primaryKey = 'id';

    protected string $primaryKeyInput = 'id';

    protected array $columns = [
        'numero' => 'numero',
        'complemento' => 'complemento',
        'referencia' => 'referencia',
        'filial_id' => 'Filial_id',
        'cep' => 'CEP',
    ];

    protected array $selectColumns = ['id', 'numero', 'complemento', 'referencia', 'Filial_id', 'CEP'];

    public function filialExiste(int $id): bool
    {
        return $this->registroExiste('Filial', 'id', $id);
    }

    public function cepExiste(string $cep): bool
    {
        return $this->registroExiste('CEP', 'CEP', $cep);
    }
}
