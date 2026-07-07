<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

abstract class SimpleCrudService
{
    protected string $table;

    protected string $primaryKey;

    protected string $primaryKeyInput;

    protected bool $autoIncrement = true;

    /** @var array<string, string> */
    protected array $columns = [];

    /** @var list<string> */
    protected array $selectColumns = [];

    protected string $orderBy;

    public function listar(): array
    {
        return DB::select(sprintf(
            'SELECT %s FROM %s ORDER BY %s',
            implode(', ', $this->selectColumns()),
            $this->table,
            $this->orderBy()
        ));
    }

    public function buscarPorId(int|string $id): ?object
    {
        return DB::selectOne(sprintf(
            'SELECT %s FROM %s WHERE %s = ?',
            implode(', ', $this->selectColumns()),
            $this->table,
            $this->primaryKey
        ), [$id]);
    }

    public function existe(int|string $id): bool
    {
        return DB::selectOne(sprintf(
            'SELECT %s FROM %s WHERE %s = ?',
            $this->primaryKey,
            $this->table,
            $this->primaryKey
        ), [$id]) !== null;
    }

    public function valorExiste(string $column, int|string $value, int|string|null $ignorarId = null): bool
    {
        if ($ignorarId !== null) {
            return DB::selectOne(sprintf(
                'SELECT %s FROM %s WHERE %s = ? AND %s != ?',
                $this->primaryKey,
                $this->table,
                $column,
                $this->primaryKey
            ), [$value, $ignorarId]) !== null;
        }

        return DB::selectOne(sprintf(
            'SELECT %s FROM %s WHERE %s = ?',
            $this->primaryKey,
            $this->table,
            $column
        ), [$value]) !== null;
    }

    public function criar(array $data): int|string
    {
        $columns = $this->insertColumns($data);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        DB::insert(sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->table,
            implode(', ', array_values($columns)),
            $placeholders
        ), $this->valuesFor($data, array_keys($columns)));

        if ($this->autoIncrement) {
            return (int) DB::getPdo()->lastInsertId();
        }

        return $data[$this->primaryKeyInput];
    }

    public function atualizar(int|string $id, array $data): int
    {
        $columns = $this->updateColumns($data);
        $assignments = array_map(
            fn (string $column): string => "{$column} = ?",
            array_values($columns)
        );
        $values = $this->valuesFor($data, array_keys($columns));
        $values[] = $id;

        return DB::update(sprintf(
            'UPDATE %s SET %s WHERE %s = ?',
            $this->table,
            implode(', ', $assignments),
            $this->primaryKey
        ), $values);
    }

    public function remover(int|string $id): int
    {
        return DB::delete(sprintf(
            'DELETE FROM %s WHERE %s = ?',
            $this->table,
            $this->primaryKey
        ), [$id]);
    }

    public function registroExiste(string $table, string $column, int|string $value): bool
    {
        return DB::selectOne(sprintf(
            'SELECT %s FROM %s WHERE %s = ?',
            $column,
            $table,
            $column
        ), [$value]) !== null;
    }

    /**
     * @return list<string>
     */
    protected function selectColumns(): array
    {
        if ($this->selectColumns !== []) {
            return $this->selectColumns;
        }

        return array_values($this->columns);
    }

    protected function orderBy(): string
    {
        return $this->orderBy ?? $this->primaryKey;
    }

    /**
     * @return array<string, string>
     */
    protected function insertColumns(array $data): array
    {
        return array_filter(
            $this->columns,
            fn (string $input): bool => array_key_exists($input, $data),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @return array<string, string>
     */
    protected function updateColumns(array $data): array
    {
        return array_filter(
            $this->columns,
            fn (string $input): bool => $input !== $this->primaryKeyInput && array_key_exists($input, $data),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @param list<string> $inputs
     * @return list<mixed>
     */
    private function valuesFor(array $data, array $inputs): array
    {
        return array_map(
            fn (string $input): mixed => $data[$input],
            $inputs
        );
    }
}
