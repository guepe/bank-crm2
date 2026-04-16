<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\HttpOptions;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Twig\Environment;

class BrevoMailer
{
    public function __construct(
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
        private readonly bool $enabled,
        private readonly ?string $apiKey,
        private readonly string $senderEmail,
        private readonly string $senderName,
        private readonly string $baseUrl,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function sendTemplatedEmail(
        string $toEmail,
        string $subject,
        string $template,
        array $context = [],
        ?Address $to = null,
    ): void {
        if (!$this->enabled) {
            $this->logger->info('Brevo mail disabled, skipping transactional email send.', [
                'to' => $toEmail,
                'subject' => $subject,
                'template' => $template,
            ]);

            return;
        }

        if ($this->apiKey === null || trim($this->apiKey) === '') {
            throw new \RuntimeException('Brevo est active mais BREVO_API_KEY est absente.');
        }

        $htmlContent = $this->twig->render($template, $context);
        $textContent = trim(preg_replace('/\s+/', ' ', strip_tags($htmlContent)) ?? '');

        $recipient = ['email' => $toEmail];
        if ($to !== null && $to->getName() !== '') {
            $recipient['name'] = $to->getName();
        }

        $options = (new HttpOptions())
            ->setHeaders([
                'accept' => 'application/json',
                'api-key' => $this->apiKey,
                'content-type' => 'application/json',
            ])
            ->setJson([
                'sender' => [
                    'email' => $this->senderEmail,
                    'name' => $this->senderName,
                ],
                'to' => [$recipient],
                'subject' => $subject,
                'htmlContent' => $htmlContent,
                'textContent' => $textContent !== '' ? $textContent : null,
            ]);

        try {
            $response = HttpClient::create()->request('POST', rtrim($this->baseUrl, '/').'/smtp/email', $options->toArray());
            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode >= 300) {
                throw new \RuntimeException(sprintf('Brevo a retourne un statut HTTP %d.', $statusCode));
            }
        } catch (
            TransportExceptionInterface|
            ClientExceptionInterface|
            RedirectionExceptionInterface|
            ServerExceptionInterface $exception
        ) {
            $this->logger->error('Brevo transactional email failed.', [
                'to' => $toEmail,
                'subject' => $subject,
                'template' => $template,
                'exception' => $exception,
            ]);

            throw new \RuntimeException('L\'envoi de l\'e-mail via Brevo a echoue.', 0, $exception);
        }
    }
}
