<?php

// src/Controller/MailController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailController extends AbstractController
{
    public function sendTestEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('kayzeurdylan@gmail.com')
            ->to('destinataire@example.com')
            ->subject('Test d\'envoi d\'e-mail')
            ->text('Ceci est un e-mail de test envoyé depuis Symfony.');

        $mailer->send($email);

        return new Response('E-mail envoyé avec succès !');
    }
}
