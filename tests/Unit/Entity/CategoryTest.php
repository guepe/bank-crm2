<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Category;

class CategoryTest extends EntityTestCase
{
    public function testNameIsTrimmedAndUsedAsStringRepresentation(): void
    {
        $category = new Category();

        $this->assertFluent($category, $category->setName('  Savings  '));

        self::assertSame('Savings', $category->getName());
        self::assertSame('Savings', (string) $category);
    }

    public function testParentChildRelationStaysConsistent(): void
    {
        $parent = (new Category())->setName('Parent');
        $child = (new Category())->setName('Child');

        $parent->addChild($child);

        self::assertSame($parent, $child->getParent());
        self::assertTrue($parent->getChildren()->contains($child));

        $parent->removeChild($child);

        self::assertNull($child->getParent());
        self::assertFalse($parent->getChildren()->contains($child));
    }
}
