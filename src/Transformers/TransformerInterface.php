<?php

namespace App\Transformers;

interface TransformerInterface
{
    /**
     * Returns the transformed data.
     *
     * @return array
     */
    public function transform(): array;

    /**
     * Returns the extracted data.
     *
     * @return array
     * @throws MissingSourceFileException  Thrown when the source file is not found at the expected path.
     */
    public function extract(): array;
}
