<?php

namespace App\Repositories;

use App\Db\AdapterAwareTrait;
use App\Db\AdapterInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    use AdapterAwareTrait;

    final public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }
}
