<?php

declare(strict_types=1);

namespace App\Services;

class UsuarioEnderecoService extends SimpleCrudService
{
    protected string $table = 'Usuario_endereco';

    protected string $primaryKey = 'Usuario_id';

    protected string $primaryKeyInput = 'usuario_id';

    protected bool $autoIncrement = false;

    protected array $columns = [
        'usuario_id' => 'Usuario_id',
        'numero' => 'numero',
        'complemento' => 'complemento',
        'referencia' => 'referencia',
        'cep' => 'CEP',
    ];

    public function usuarioExiste(int $id): bool
    {
        return $this->registroExiste('Usuario', 'id', $id);
    }

    public function cepExiste(string $cep): bool
    {
        return $this->registroExiste('CEP', 'CEP', $cep);
    }
}
