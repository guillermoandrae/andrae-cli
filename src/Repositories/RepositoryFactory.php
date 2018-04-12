<?php

namespace App\Repositories;

use App\Db\ClientInterface;

class RepositoryFactory
{
    public static function factory($name, ClientInterface $client): RepositoryInterface
    {
        try {
            $className = sprintf('%s\%sRepository', __NAMESPACE__, ucfirst(strtolower($name)));
            $reflectionClass = new \ReflectionClass($className);
            $class = $reflectionClass->newInstance($client);
            return $class;
        } catch (\Exception $ex) {
            throw new \ErrorException(sprintf('The %s repository does not exist.', $name));
        }
    }
}
