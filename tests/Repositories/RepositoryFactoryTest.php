<?php

namespace Tests\Repositories;

use App\Db\AdapterInterface;
use App\Repositories\InvalidRepositoryException;
use App\Repositories\RepositoryFactory;
use App\Repositories\RepositoryInterface;
use PHPUnit\Framework\TestCase;

class RepositoryFactoryTest extends TestCase
{
    public function testFactory()
    {
        $repository = RepositoryFactory::factory('posts', $this->getMockAdapter());
        $this->assertInstanceOf(RepositoryInterface::class, $repository);
    }

    public function testFactoryWithInvalidRepository()
    {
        $this->expectException(InvalidRepositoryException::class);
        RepositoryFactory::factory('guillermo', $this->getMockAdapter());
    }

    private function getMockAdapter(): AdapterInterface
    {
        return $this->getMockForAbstractClass(AdapterInterface::class);
    }
}
