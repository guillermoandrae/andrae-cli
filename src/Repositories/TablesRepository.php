<?php

namespace App\Repositories;

class TablesRepository extends AbstractRepository
{
    public function create(array $data)
    {

    }

    public function delete($name)
    {

    }

    public function show()
    {
        return $this->getClient()->showTables();
    }

    public function describe($name)
    {
        return $this->getClient()->describeTable($name);
    }

    public function glance($table)
    {
        return $this->getClient()->select($table, 10);
    }
}
