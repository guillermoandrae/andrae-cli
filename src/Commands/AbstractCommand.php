<?php

namespace App\Commands;

use App\Db\ClientAwareTrait;
use App\Db\ClientInterface;
use App\Repositories\RepositoryFactory;
use App\Repositories\RepositoryInterface;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    use ClientAwareTrait;

    /**
     * AbstractCommand constructor.
     *
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
        parent::__construct();
    }

    public function getRepository($name): RepositoryInterface
    {
        try {
            return RepositoryFactory::factory($name, $this->getClient());
        } catch (\Exception $ex) {

        }
    }
}
