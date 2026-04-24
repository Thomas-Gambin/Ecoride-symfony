<?php

declare(strict_types=1);

namespace App\Service\Mail;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

final class WelcomeEmailService
{
    public function __construct(
        private readonly MailerSendClient $client,
        private readonly Environment $twig,
        #[Autowire('%env(MAIL_FROM)%')]
        private readonly string $fromEmail,
    ) {}

    public function send(string $toEmail, string $pseudo, int $credits): void
    {
        $html = $this->twig->render('emails/welcome.html.twig', [
            'pseudo' => $pseudo,
            'credits' => $credits,
        ]);

        $this->client->send([
            'from' => ['email' => $this->fromEmail, 'name' => 'EcoRide'],
            'to' => [['email' => $toEmail]],
            'subject' => 'Bienvenue sur EcoRide',
            'html' => $html,
        ]);
    }
}

