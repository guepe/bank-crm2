<?php

namespace App\Tests\Unit\Entity;

use App\Entity\FileLinked;

class FileLinkedTest extends EntityTestCase
{
    public function testTypeAndNameAreTrimmed(): void
    {
        $file = new FileLinked();

        $this->assertFluent($file, $file->setType('  pdf  '));
        $this->assertFluent($file, $file->setName('  contract.pdf  '));

        self::assertSame('pdf', $file->getType());
        self::assertSame('contract.pdf', $file->getName());
    }

    public function testFiledataCanStoreRawPayload(): void
    {
        $payload = fopen('php://memory', 'rb+');
        fwrite($payload, 'demo');
        rewind($payload);

        $file = (new FileLinked())->setFiledata($payload);

        self::assertSame($payload, $file->getFiledata());

        fclose($payload);
    }
}
