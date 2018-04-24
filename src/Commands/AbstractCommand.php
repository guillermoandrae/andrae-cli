<?php

namespace App\Commands;

use App\Db\AdapterAwareTrait;
use App\Db\AdapterInterface;
use App\Repositories\InvalidRepositoryException;
use App\Repositories\RepositoryFactory;
use App\Repositories\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    use AdapterAwareTrait;

    /**
     * AbstractCommand constructor.
     *
     * @param AdapterInterface $client
     */
    public function __construct(AdapterInterface $client)
    {
        $this->adapter = $client;
        parent::__construct();
    }

    /**
     * Returns the desired repository.
     *
     * @param string $name  The name of the repository
     * @return RepositoryInterface|null
     * @throws InvalidRepositoryException  Thrown when a non-existent repository is requested.
     */
    public function getRepository(string $name): RepositoryInterface
    {
        return RepositoryFactory::factory($name, $this->getAdapter());
    }

    /**
     * Outputs the desired failure message.
     *
     * @param OutputInterface $output  The output instance.
     * @param string $message  The failure message.
     */
    protected function fail(OutputInterface $output, string $message)
    {
        $output->writeln(
            sprintf('<fg=red;options=bold>An error occurred:</> <fg=red>%s</>', $message)
        );
        echo PHP_EOL;
    }
}
