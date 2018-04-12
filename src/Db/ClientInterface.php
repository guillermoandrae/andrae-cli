<?php

namespace App\Db;

interface ClientInterface
{
    public function insert($table, array $data): bool;

    public function update($table, $field, $value, array $data): array;

    public function delete($table, $field, $value): bool;

    public function selectOne($table, $field, $value): array;

    public function select($table, $limit): array;

    public function selectWhere($table, $field, $value, $limit): array;

    public function showTables(): array;

    public function describeTable($table): array;
}
