<?php

namespace App\Tests\Integration;

use App\Entity\BetaPilotageIncident;
use App\Entity\FieldEdit;
use App\Entity\OnboardingSession;
use App\Entity\Tenant;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BetaPilotageFlowTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $this->resetDatabase();
    }

    public function testAdminCanManageTenantsPlansAndSeeAggregatedPilotage(): void
    {
        $admin = $this->createUser('admin-pilotage', ['ROLE_ADMIN'], 'admin-pilotage@example.test');
        $this->client->loginUser($admin);

        $this->client->request('GET', '/tenants/new');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Enregistrer', [
            'tenant[name]' => 'Cabinet Alpha',
            'tenant[code]' => 'ALPHA',
            'tenant[plan]' => Tenant::PLAN_PRO,
            'tenant[status]' => Tenant::STATUS_ACTIVE,
            'tenant[betaContactEmail]' => 'referent.alpha@example.test',
            'tenant[notes]' => 'Tenant pilote mai 2026',
        ]);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        $this->assertResponseContains('Cabinet Alpha');
        $this->assertResponseContains('Pro');

        /** @var Tenant $tenant */
        $tenant = $this->entityManager->getRepository(Tenant::class)->findOneBy(['code' => 'alpha']);
        self::assertNotNull($tenant);

        $clientUser = $this->createUser('client-alpha', ['ROLE_CLIENT'], 'client-alpha@example.test', $tenant, acceptConsent: true);
        $this->createSession($clientUser, OnboardingSession::STATUS_COMPLETED, [
            'client' => [
                'prenom' => 'Nina',
                'age' => 41,
                'statut' => 'mariee',
                'pro' => 'independante',
                'attente' => 'Secret patrimoine prive',
            ],
            'projets' => [
                'vision' => 'Transmettre progressivement',
                'retraite_age' => 62,
                'objectifs' => ['Transmission'],
            ],
            'risque' => [
                'profil' => 'equilibre',
                'transmission' => 'Donation familiale',
            ],
            'etapes' => [
                'etapes' => ['Donation 2028'],
                'etape_cle' => 'Donation 2028',
            ],
            'patrimoine' => [
                'immo' => 'Maison familiale confidentielle',
                'tresorerie' => 40000,
                'financier' => 'Assurance vie',
            ],
        ]);

        $abandonedUser = $this->createUser('client-abandon', ['ROLE_CLIENT'], 'client-abandon@example.test', $tenant, acceptConsent: true);
        $this->createSession($abandonedUser, OnboardingSession::STATUS_ABANDONED, [
            'client' => [
                'prenom' => 'Paul',
            ],
        ]);

        $this->createUser('client-consent', ['ROLE_CLIENT'], 'client-consent@example.test', $tenant, acceptConsent: false);

        $this->client->request('GET', '/pilotage');
        self::assertResponseIsSuccessful();
        $this->assertResponseContains('Pilotage beta');
        $this->assertResponseContains('Cabinet Alpha');
        $this->assertResponseContains('Completude moyenne');
        $this->assertResponseContains('Relances beta');
        $this->assertResponseContains('Qualite extraction');
        $this->assertResponseContains('Consentement manquant');
        $this->assertResponseNotContains('Secret patrimoine prive');
        $this->assertResponseNotContains('Maison familiale confidentielle');
        $this->assertResponseNotContains('client-alpha@example.test');

        $crawler = $this->client->request('GET', sprintf('/tenants/%d', $tenant->getId()));
        $token = $crawler->filter(sprintf('form[action="/tenants/%d/status"] input[name="_token"]', $tenant->getId()))->attr('value');
        $this->client->request('POST', sprintf('/tenants/%d/status', $tenant->getId()), [
            '_token' => $token,
        ]);

        self::assertResponseRedirects(sprintf('/tenants/%d', $tenant->getId()));
        $this->entityManager->clear();
        /** @var Tenant $suspendedTenant */
        $suspendedTenant = $this->entityManager->getRepository(Tenant::class)->find($tenant->getId());
        self::assertSame(Tenant::STATUS_SUSPENDED, $suspendedTenant->getStatus());
    }

    public function testAdminCanCreateAndResolvePilotageIncident(): void
    {
        $tenant = (new Tenant())
            ->setName('Cabinet Beta')
            ->setCode('beta')
            ->setPlan(Tenant::PLAN_BETA);
        $this->entityManager->persist($tenant);
        $this->entityManager->flush();

        $admin = $this->createUser('admin-incident', ['ROLE_ADMIN'], 'admin-incident@example.test');
        $this->client->loginUser($admin);

        $this->client->request('GET', '/pilotage/incidents/new');
        self::assertResponseIsSuccessful();
        $this->client->submitForm('Enregistrer', [
            'beta_pilotage_incident[title]' => 'Extraction a surveiller',
            'beta_pilotage_incident[tenant]' => (string) $tenant->getId(),
            'beta_pilotage_incident[category]' => BetaPilotageIncident::CATEGORY_EXTRACTION,
            'beta_pilotage_incident[severity]' => BetaPilotageIncident::SEVERITY_HIGH,
            'beta_pilotage_incident[status]' => BetaPilotageIncident::STATUS_OPEN,
            'beta_pilotage_incident[summary]' => 'Signal faible sur une extraction, sans contenu client.',
        ]);

        self::assertResponseRedirects('/pilotage');
        $this->client->followRedirect();
        $this->assertResponseContains('Extraction a surveiller');
        $this->assertResponseContains('Qualite extraction');
        $this->assertResponseContains('Haute');

        /** @var BetaPilotageIncident $incident */
        $incident = $this->entityManager->getRepository(BetaPilotageIncident::class)->findOneBy(['title' => 'Extraction a surveiller']);
        self::assertNotNull($incident);

        $crawler = $this->client->request('GET', '/pilotage');
        $token = $crawler->filter(sprintf('form[action="/pilotage/incidents/%d/resolve"] input[name="_token"]', $incident->getId()))->attr('value');
        $this->client->request('POST', sprintf('/pilotage/incidents/%d/resolve', $incident->getId()), [
            '_token' => $token,
            'resolution_notes' => 'Traite dans la boucle produit.',
        ]);

        self::assertResponseRedirects('/pilotage');
        $this->entityManager->clear();
        /** @var BetaPilotageIncident $resolvedIncident */
        $resolvedIncident = $this->entityManager->getRepository(BetaPilotageIncident::class)->find($incident->getId());
        self::assertSame(BetaPilotageIncident::STATUS_RESOLVED, $resolvedIncident->getStatus());
        self::assertSame('Traite dans la boucle produit.', $resolvedIncident->getResolutionNotes());
    }

    public function testSuspendedUserAndSuspendedTenantCannotLogin(): void
    {
        $tenant = (new Tenant())
            ->setName('Cabinet Suspendu')
            ->setCode('suspendu')
            ->setPlan(Tenant::PLAN_BETA);
        $this->entityManager->persist($tenant);
        $this->entityManager->flush();

        $suspendedUser = $this->createUser(
            'client-suspendu',
            ['ROLE_CLIENT'],
            'client-suspendu@example.test',
            $tenant,
            password: 'Password123!',
            acceptConsent: true,
        );
        $suspendedUser->suspend('Fin de pilote.');
        $this->entityManager->flush();

        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', [
            'username' => 'client-suspendu',
            'password' => 'Password123!',
        ]);
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertResponseContains('Ce compte est suspendu');

        /** @var Tenant $tenant */
        $tenant = $this->entityManager->getRepository(Tenant::class)->find($tenant->getId());
        $tenant->setStatus(Tenant::STATUS_SUSPENDED);
        $this->entityManager->flush();
        $this->createUser(
            'client-tenant-suspendu',
            ['ROLE_CLIENT'],
            'client-tenant-suspendu@example.test',
            $tenant,
            password: 'Password123!',
            acceptConsent: true,
        );
        $this->client->restart();

        $this->client->request('GET', '/login');
        $this->client->submitForm('Se connecter', [
            'username' => 'client-tenant-suspendu',
            'password' => 'Password123!',
        ]);
        self::assertResponseRedirects('/login');
        $this->client->followRedirect();
        $this->assertResponseContains('tenant rattache');
    }

    private function createUser(
        string $username,
        array $roles,
        string $email,
        ?Tenant $tenant = null,
        string $password = 'Password123!',
        bool $acceptConsent = true,
    ): User {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($email)
            ->setRoles($roles)
            ->setEnabled(true)
            ->setTenant($tenant);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->markEmailVerified();

        if ($acceptConsent) {
            $user->acceptConsent('planilife-beta-2026-05');
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createSession(User $user, string $status, array $data): OnboardingSession
    {
        $session = new OnboardingSession($user);
        $session->setStatus($status);
        $session->setExtractedData($data);

        $this->entityManager->persist($session);
        $this->entityManager->persist(new FieldEdit(
            $session,
            'client.prenom',
            (string) ($data['client']['prenom'] ?? 'A completer'),
            FieldEdit::SOURCE_DETECTED,
        ));
        $this->entityManager->flush();

        return $session;
    }

    private function resetDatabase(): void
    {
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->entityManager);

        if ($metadata === []) {
            return;
        }

        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    private function assertResponseContains(string $text): void
    {
        self::assertStringContainsString($text, (string) $this->client->getResponse()->getContent());
    }

    private function assertResponseNotContains(string $text): void
    {
        self::assertStringNotContainsString($text, (string) $this->client->getResponse()->getContent());
    }
}
