<?php

namespace App\Tests\Unit\Entity;

use App\Entity\OnboardingSession;
use App\Entity\User;

class OnboardingSessionTest extends EntityTestCase
{
    public function testDefaultStateIsInitialized(): void
    {
        $session = new OnboardingSession((new User())->setUsername('advisor'));

        self::assertSame(OnboardingSession::STATUS_IN_PROGRESS, $session->getStatus());
        self::assertSame(OnboardingSession::PHASE_DISCOVERY, $session->getPhase());
        self::assertSame([], $session->getMessages());
        self::assertSame([], $session->getExtractedData());
        self::assertSame(0.0, $session->getCompleteness());
    }

    public function testAddMessageAndExtractedDataUpdatesPayloads(): void
    {
        $session = new OnboardingSession((new User())->setUsername('advisor'));

        $session->addMessage('user', 'Bonjour');
        $session->setExtractedData(['email' => 'jane@example.com']);
        $session->updateExtractedData(['phone' => '0123456789']);

        self::assertCount(1, $session->getMessages());
        self::assertSame('user', $session->getMessages()[0]['role']);
        self::assertSame('Bonjour', $session->getMessages()[0]['content']);
        self::assertArrayHasKey('timestamp', $session->getMessages()[0]);
        self::assertSame(
            ['email' => 'jane@example.com', 'phone' => '0123456789'],
            $session->getExtractedData()
        );
    }

    public function testInvalidStatusAndPhaseAreRejected(): void
    {
        $session = new OnboardingSession((new User())->setUsername('advisor'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status: archived');

        $session->setStatus('archived');
    }

    public function testInvalidPhaseIsRejected(): void
    {
        $session = new OnboardingSession((new User())->setUsername('advisor'));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid phase: closing');

        $session->setPhase('closing');
    }
}
