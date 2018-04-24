<?php

namespace App\Transformers;

abstract class AbstractTransformer implements TransformerInterface
{
    protected $sourcesDir = '';

    final public function __construct()
    {
        $this->sourcesDir = sprintf('%s/database/sources', dirname(dirname(__DIR__)));
    }
}
