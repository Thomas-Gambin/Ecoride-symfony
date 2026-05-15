<?php

declare(strict_types=1);

namespace App\Service\Mail;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * En dev Docker : Mailpit (USE_MAILPIT=1) — pas de limite de destinataires.
 * Sinon : API MailerSend (production / compte trial).
 */
final class OutboundEmailSender
{
    public function __construct(
        private readonly MailerInterface $symfonyMailer,
        private readonly MailerSendClient $mailerSend,
        #[Autowire('%env(bool:USE_MAILPIT)%')]
        private readonly bool $useMailpit,
        #[Autowire('%env(MAIL_FROM)%')]
        private readonly string $fromEmail,
    ) {}

    public function send(string $toEmail, string $subject, string $html): void
    {
        if ($this->useMailpit) {
            $this->symfonyMailer->send(
                (new Email())
                    ->from(sprintf('EcoRide <%s>', $this->fromEmail))
                    ->to($toEmail)
                    ->subject($subject)
                    ->html($html)
            );

            return;
        }

        $this->mailerSend->send([
            'from' => ['email' => $this->fromEmail, 'name' => 'EcoRide'],
            'to' => [['email' => $toEmail]],
            'subject' => $subject,
            'html' => $html,
        ]);
    }
}
