<?php

namespace App\Commands;

use App\Commands\Helpers\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
                'The name of the table operation: show, describe, glance'
            )
            ->addArgument(
                'table',
                InputArgument::OPTIONAL,
                'The name of the table on which you are operating'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $operation = $input->getArgument('operation');
            $table = $input->getArgument('table');
            if ($operation != 'show') {
                if (is_null($table)) {
                    throw new \InvalidArgumentException('Please provide a table name');
                }
            }
            if (!method_exists($this, $operation)) {
                throw new \InvalidArgumentException(
                    sprintf("'%s' is an invalid operation", $operation)
                );
            }
            return call_user_func_array([$this, $operation], [$output, $table]);
        } catch (\Exception $ex) {
            $this->fail($output, $ex->getMessage());
        }
    }

    protected function show(OutputInterface $output)
    {
        $result = $this->getAdapter()->listTables();
        $tables = [];
        foreach ($result as $table) {
            $tables[] = [$table];
        }
        TableHelper::render($output, ['Tables'], $tables);
    }

    protected function describe(OutputInterface $output, $table)
    {
        $headers = ['Field', 'Type', 'Index'];
        $result = $this->getAdapter()->describeTable($table);
        TableHelper::render($output, $headers, $result);
    }

    protected function glance(OutputInterface $output, $table)
    {
        $result = $this->getAdapter()->fetchItems($table);
        $rows = [];
        foreach ($result as $item) {
            $rows[] = array_map(function (&$value) {
                $length = 20;
                if (strlen($value) > $length) {
                    $value = substr_replace($value, '...', $length);
                }
                return $value;
            }, $item);
        }
        TableHelper::render($output, array_keys($result[0]), $rows);
    }
}
