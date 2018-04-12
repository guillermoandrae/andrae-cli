<?php

namespace App\Repositories;

use App\Db\ClientAwareTrait;
use App\Db\ClientInterface;

abstract class AbstractRepository implements RepositoryInterface
{
    use ClientAwareTrait;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function create(array $data)
    {
        // TODO: Implement create() method.
    }
}