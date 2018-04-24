<?php

namespace App\Transformers;

class TransformerFactory
{
    /**
     * Returns the desired repository.
     *
     * @param string $name  The name of the transformer.
     * @return TransformerInterface  The transformer instance.
     * @throws InvalidTransformerException  Thrown when a non-existent transformer is requested.
     */
    public static function factory(string $name): TransformerInterface
    {
        try {
            $className = sprintf(
                '%s\%sTransformer',
                __NAMESPACE__,
                ucfirst(strtolower($name))
            );
            $reflectionClass = new \ReflectionClass($className);
            $class = $reflectionClass->newInstance();
            return $class;
        } catch (\Exception $ex) {
            throw new InvalidTransformerException(
                sprintf('The %s transformer does not exist.', $name)
            );
        }
    }
}
