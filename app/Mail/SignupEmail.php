<?php

declare(strict_types=1);

namespace App\Mail;

use App\Config;
use App\Entity\User;
use App\SignedUrl;
use Slim\Interfaces\RouteParserInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\BodyRendererInterface;

class SignupEmail
{
    public function __construct(
        private readonly Config $config,
        private readonly MailerInterface $mailer,
        private readonly BodyRendererInterface $renderer,
        private readonly SignedUrl $signedUrl,
    ) {
    }

    /**
     * @param  string  $to
     *
     * @return void
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function send(User $user): void
    {
        $email = $user->getEmail();
        $expirationDate = new \DateTime('+30 minutes');
        $activationLink = $this->signedUrl->fromRoute(
            routeName:'verify',
            routeParams: [
                'id' => $user->getId(),
                'hash' => sha1($email)
            ],
            expirationDate: $expirationDate
        );

        $message = (new TemplatedEmail())
            ->from($this->config->get('mailer.from'))
            ->to($email)
            ->subject('Welcome to '.$this->config->get('app_name'))
            ->htmlTemplate('emails/signup.html.twig')
            ->context([
                'activationLink' => $activationLink,
                'expirationDate' => $expirationDate,
            ]);

        $this->renderer->render($message);
        $this->mailer->send($message);
    }
}