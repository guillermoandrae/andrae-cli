<?php

namespace App\Transformers;

class TwitterTransformer extends AbstractTransformer
{
    public function transform(): array
    {
        $this->extract();

    }

    public function extract(): array
    {
        $path = sprintf('%s/tweets.csv', $this->sourcesDir);
        if (!file_exists($path)) {
            throw new MissingSourceFileException(
                sprintf('The source file was not found at %s.', $path)
            );
        }
        $extractedData = [];

        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $num = count($data);
                echo "<p> $num fields in line $row: <br /></p>\n";
                $row++;
                for ($c=0; $c < $num; $c++) {
                    echo $data[$c] . "<br />\n";
                }
            }
            fclose($handle);
        }

        dd($extractedData);
        return $extractedData;
    }

}
