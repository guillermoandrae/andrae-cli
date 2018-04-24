<?php

namespace Test\Models;

use App\Models\AbstractModel;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    public function testId()
    {
        $expectedId = '3';
        $model = $this->getMockForAbstractClass(
            AbstractModel::class,
            [['id' => $expectedId]]
        );
        $this->assertSame($expectedId, $model->getId());
    }
}
