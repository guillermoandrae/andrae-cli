<?php

namespace App\Db;

trait AdapterAwareTrait
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Returns the registered adapter.
     *
     * @return AdapterInterface
     */
    final public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }
}
