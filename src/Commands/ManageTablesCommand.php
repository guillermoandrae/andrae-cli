<?php

namespace App\Commands;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ManageTablesCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('manage:tables')
            ->setDescription('Manages the tables')
            ->setHelp('This command allows you to perform management tasks on the database tables.')
            ->addArgument(
                'operation',
                InputArgument::REQUIRED,
                'The name of the table operation')
            ->addArgument(
                'table',
                InputArgument::OPTIONAL,
                'The name of the table on which you are operating');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $operation = $input->getArgument('operation');
        $table = $input->getArgument('table');
        if ($operation == 'glance') {
            return $this->glance($input, $output, $table);
        }
        return call_user_func_array([$this, $operation], [$output, $table]);
    }

    protected function show(OutputInterface $output)
    {
        $result = $this->getRepository('tables')->show();
        foreach ($result as $table) {
            $output->writeln($table) . PHP_EOL;
        }
    }

    protected function describe(OutputInterface $output, $table)
    {
        $result = $this->getRepository('tables')->describe($table);
        $table = new Table($output);
        $table
            ->setHeaders($result['headers'])
            ->setRows($result['rows']);
        $table->render();
    }

    protected function glance(InputInterface $input, OutputInterface $output, $table)
    {
        $result = $this->getRepository('tables')->glance($table);
        $table = new Table($output);
        $table
            ->setHeaders($result['headers'])
            ->setRows($result['rows']);
        $table->render();
    }
}
