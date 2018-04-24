<?php

namespace App\Repositories;

use App\Db\AdapterInterface;

class RepositoryFactory
{
    /**
     * Returns the desired repository.
     *
     * @param string $name  The name of the repository.
     * @param AdapterInterface $adapter  The database client.
     * @return RepositoryInterface  The repository instance.
     * @throws InvalidRepositoryException  Thrown when a non-existent repository is requested.
     */
    public static function factory(string $name, AdapterInterface $adapter): RepositoryInterface
    {
        try {
            $className = sprintf(
                '%s\%sRepository',
                __NAMESPACE__,
                ucfirst(strtolower($name))
            );
            $reflectionClass = new \ReflectionClass($className);
            $class = $reflectionClass->newInstance($adapter);
            return $class;
        } catch (\Exception $ex) {
            throw new InvalidRepositoryException(
                sprintf('The %s repository does not exist.', $name)
            );
        }
    }
}
