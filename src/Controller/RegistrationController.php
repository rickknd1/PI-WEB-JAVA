<?php

// src/Controller/RegistrationController.php
namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('password')->getData();

            // Définit le username avec la valeur de l'email
            $user->setUsername($user->getEmail());

            // Encoder le mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Générer un token de vérification
            $verificationToken = bin2hex(random_bytes(32));
            $user->setVerificationToken($verificationToken);

            // Persister l'utilisateur en base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Envoyer l'e-mail de vérification
            $verificationUrl = $this->generateUrl('app_verify_email', ['token' => $verificationToken], UrlGeneratorInterface::ABSOLUTE_URL);

            $email = (new Email())
                ->from('no-reply@example.com')
                ->to($user->getEmail())
                ->subject('Vérification de votre adresse e-mail')
                ->html($this->renderView('emails/verification_email.html.twig', [
                    'verificationUrl' => $verificationUrl,
                ]));

            $mailer->send($email);

            // Rediriger vers une page d'attente
            return $this->redirectToRoute('app_waiting_verification');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify-email/{token}', name: 'app_verify_email')]
    public function verifyUserEmail(string $token, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(User::class)->findOneBy(['verificationToken' => $token]);

        if (!$user) {
            throw $this->createNotFoundException('Token de vérification invalide.');
        }

        // Activer le compte utilisateur
        $user->setIsVerified(true);
        $user->setVerificationToken(null); // Supprimer le token après vérification
        $entityManager->flush();

        // Rediriger vers la page de connexion avec un message de succès
        $this->addFlash('success', 'Votre adresse e-mail a été vérifiée avec succès. Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/waiting-verification', name: 'app_waiting_verification')]
    public function waitingVerification(): Response
    {
        return $this->render('emails/waiting_verification.html.twig');
    }
}
