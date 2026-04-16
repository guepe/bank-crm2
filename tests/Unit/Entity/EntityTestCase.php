<?php

namespace App\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;

abstract class EntityTestCase extends TestCase
{
    protected function assertFluent(object $entity, object $result): void
    {
        self::assertSame($entity, $result);
    }
}
