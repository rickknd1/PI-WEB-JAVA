<?php

namespace App\EventListener;

use App\Event\CommunityCreatedEvent;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\MailerInterface;

#[AsEventListener(event: CommunityCreatedEvent::NAME)]
final class NewCommunityListener
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer){
        $this->mailer = $mailer;
    }

    public function __invoke(CommunityCreatedEvent $event){
        $community = $event->getCommunity();
        $user = $event->getUser();

        $mail = (new TemplatedEmail())
            ->to('admin@syncylinky.tn')
            ->from($user->getEmail())
            ->subject('New Community')
            ->htmlTemplate('emails/new.community.html.twig')
            ->context([
                'data' => $community,
                'user' => $user,
            ]);
        $this->mailer->send($mail);
    }

}
