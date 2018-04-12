<?php

namespace App\Db;

trait ClientAwareTrait
{
    /**
     * @var ClientInterface
     */
    protected $client;

    final public function getClient(): ClientInterface
    {
        return $this->client;
    }
}