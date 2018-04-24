<?php

namespace App\Commands;

use App\Repositories\RepositoryFactory;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ManageDatabaseCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('manage:db')
            ->setDescription('Manages a local instance of DynamoDB')
            ->setHelp('This command allows you to perform management tasks on a local instance of DynamoDB.')
            ->addArgument(
                'operation',
                InputArgument::REQUIRED,
                'The name of the database operation: install, start, stop, status'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            if (stristr(getenv('AWS_DYNAMODB_ENDPOINT'), 'amazonaws.com')) {
                throw new \ErrorException(
                    "<fg=red;options=bold>\"I'm sorry, Dave. I'm afraid I can't do that.\"</>"
                    . PHP_EOL
                    . 'This command is meant for use with local instances of DynamoDB. Make sure your '
                    . 'AWS_DYNAMODB_ENDPOINT environment variable points to a local address and not an AWS address. '
                    . PHP_EOL
                    . 'Operations on AWS databases should be done through the AWS management console or via the AWS '
                    . 'Command Line Interface.'
                );
            }
            $operation = $input->getArgument('operation');
            if (!method_exists($this, $operation)) {
                throw new \InvalidArgumentException(
                    sprintf("'%s' is an invalid operation", $operation)
                );
            }
            return call_user_func_array([$this, $operation], [$output]);
        } catch (\Exception $ex) {
            $this->fail($output, $ex->getMessage());
        }
    }

    protected function install(OutputInterface $output)
    {
        $output->writeln('Installing DynamoDB...');
        $this->getAdapter()->installLocalDb();
        $output->writeln('Done installing DynamoDB!');
    }

    protected function start(OutputInterface $output)
    {
        $output->writeln('Starting DynamoDB...');
        $this->getAdapter()->startLocalDb();
        $output->writeln(sprintf(
            'DynamoDB is now running! Go to %s/shell to view the interactive shell.',
            getenv('AWS_DYNAMODB_ENDPOINT')
        ));
    }

    protected function seed(OutputInterface $output)
    {
        $output->writeln('Seeding the DynamoDB database...');
        $seedsPath = dirname(dirname(__DIR__)) . '/database/seeds';
        foreach (['posts', 'nicknames'] as $table) {
            $this->getAdapter()->createTable($table, [
                'columns' => [
                    [
                        'field' => 'id',
                        'type' => 'S'
                    ]
                ],
                'keys' => [
                    [
                        'field' => 'id',
                        'type' => 'HASH'
                    ]
                ]
            ]);
            $data = file_get_contents(sprintf('%s/seeds/%s.json', $seedsPath, $table));
            RepositoryFactory::factory($table, $this->getAdapter())->create($data);
            $output->writeln(sprintf('Seeded the %s table...', $table));
        }
        $output->writeln('Done seeding the DynamoDB database.');
        $command = $this->getApplication()->find('manage:tables');
        $tablesInput = new ArrayInput([
            'operation' => 'show'
        ]);
        $command->run($tablesInput, $output);
    }

    protected function stop(OutputInterface $output)
    {
        $output->writeln('Stopping DynamoDB...');
        $this->getAdapter()->stopLocalDb();
        $output->writeln('DynamoDB is no longer running.');
    }

    protected function status(OutputInterface $output)
    {
        $output->writeln(sprintf(
            'DynamoDB is %srunning.',
            $this->getAdapter()->isLocalDbRunning() ? '': 'not '
        ));
    }
}
