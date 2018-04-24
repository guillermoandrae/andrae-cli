<?php

namespace App\Commands;

use App\Transformers\TransformerFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('import')
            ->setDescription('Imports data into the tables')
            ->setHelp('This command allows you to import data into any of the tables in the database.')
            ->addOption(
                'table',
                't',
                InputOption::VALUE_REQUIRED,
                'The name of the destination table'
            )
            ->addOption(
                'source',
                's',
                InputOption::VALUE_REQUIRED,
                'The name of a valid source'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = $input->getOption('table');
        $source = $input->getOption('source');
        try {
            $repository = $this->getRepository($table);
            $transformer = TransformerFactory::factory($source);
            $data = $transformer->transform();
            foreach ($data as $datum) {
                $repository->create($datum);
            }
        } catch (\Exception $ex) {
            $this->fail($output, $ex->getMessage());
        }
    }
}
