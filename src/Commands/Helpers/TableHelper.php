<?php

namespace App\Commands\Helpers;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class TableHelper
{
    /**
     * Renders a table.
     *
     * @param OutputInterface $output The output instance.
     * @param array $headers  The table header data.
     * @param array $rows  The table row data.
     */
    public static function render(OutputInterface $output, array $headers, array $rows)
    {
        $table = new Table($output);
        $table
            ->setHeaders($headers)
            ->setRows($rows);
        $table->render();
    }
}
