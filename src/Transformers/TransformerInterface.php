<?php

namespace App\Transformer;

interface TransformerInterface
{
    public static function transform($path): array;
}
