<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Contact;
use App\Entity\User;

class ContactTest extends EntityTestCase
{
    public function testStringRepresentationUsesFirstnameAndLastname(): void
    {
        $contact = (new Contact())
            ->setFirstname('Jane')
            ->setLastname('  Doe  ');

        self::assertSame('Jane Doe', (string) $contact);
        self::assertSame('Doe', $contact->getLastname());
    }

    public function testEmailIsNormalized(): void
    {
        $contact = (new Contact())->setEmail('  JOHN.DOE@Example.COM  ');

        self::assertSame('john.doe@example.com', $contact->getEmail());
    }

    public function testUserAccountRelationStaysInSync(): void
    {
        $contact = new Contact();
        $user = (new User())->setUsername('advisor');

        $contact->setUserAccount($user);

        self::assertSame($user, $contact->getUserAccount());
        self::assertSame($contact, $user->getContact());
    }
}
