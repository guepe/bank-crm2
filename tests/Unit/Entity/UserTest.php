<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Contact;
use App\Entity\User;

class UserTest extends EntityTestCase
{
    public function testUsernameAndEmailAreNormalized(): void
    {
        $user = (new User())
            ->setUsername('  JOHN.DOE  ')
            ->setEmail('  JOHN.DOE@Example.COM  ');

        self::assertSame('john.doe', $user->getUsername());
        self::assertSame('john.doe@example.com', $user->getEmail());
        self::assertSame('john.doe', $user->getUserIdentifier());
    }

    public function testAdminRoleAlsoExposesRoleUserAndFriendlyLabels(): void
    {
        $user = (new User())->setRoles(['ROLE_ADMIN', 'ROLE_ADMIN', '', 'ROLE_CLIENT']);

        self::assertSame(['ROLE_ADMIN', 'ROLE_CLIENT', 'ROLE_USER'], $user->getRoles());
        self::assertSame(['Administrateur', 'Client portail'], $user->getRoleLabels());
        self::assertTrue($user->isInternalUser());
        self::assertTrue($user->isClientUser());
    }

    public function testDisplayNameUsesLinkedContactWhenAvailable(): void
    {
        $contact = (new Contact())
            ->setFirstname('Jane')
            ->setLastname('Doe');
        $user = (new User())->setUsername('advisor');

        $user->setContact($contact);

        self::assertSame('Jane Doe', $user->getDisplayName());
        self::assertSame($user, $contact->getUserAccount());
    }
}
