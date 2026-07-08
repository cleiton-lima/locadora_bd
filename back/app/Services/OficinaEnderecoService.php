<?php

declare(strict_types=1);

namespace App\Services;

class OficinaEnderecoService extends SimpleCrudService
{
    protected string $table = 'Oficina_endereco';

    protected string $primaryKey = 'id';

    protected string $primaryKeyInput = 'id';

    protected array $columns = [
        'numero' => 'numero',
        'complemento' => 'complemento',
        'referencia' => 'referencia',
        'oficina_id' => 'Oficina_id',
        'cep' => 'CEP',
    ];

    protected array $selectColumns = ['id', 'numero', 'complemento', 'referencia', 'Oficina_id', 'CEP'];

    public function oficinaExiste(int $id): bool
    {
        return $this->registroExiste('Oficina', 'id', $id);
    }

    public function cepExiste(string $cep): bool
    {
        return $this->registroExiste('CEP', 'CEP', $cep);
    }
}
