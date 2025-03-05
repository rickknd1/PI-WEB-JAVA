<?php

namespace App\Controller;

use App\Service\GoogleAuthenticatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/2fa')]
class TwoFactorController extends AbstractController
{
    #[Route('/verify-google', name: 'two_factor_google_verify')]
    public function verifyGoogleAuth(
        Request $request,
        GoogleAuthenticatorService $authenticatorService,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $twoFactorCode = $request->request->get('two_factor_code');

        // Vérification du code de double authentification
        if ($authenticatorService->verifyCode($user->getGoogleAuthenticatorSecret(), $twoFactorCode)) {
            // Continuer l'authentification
            return $this->redirectToRoute('home');
        }

        // Code incorrect
        $this->addFlash('error', 'Code de double authentification incorrect');
        return $this->redirectToRoute('two_factor_google_prompt');
    }

    #[Route('/prompt', name: 'two_factor_google_prompt')]
    public function promptTwoFactor(): Response
    {
        return $this->render('two_factor/google_prompt.html.twig');
    }

    #[Route('/setup', name: 'two_factor_setup')]
    public function setupTwoFactor(
        GoogleAuthenticatorService $authenticatorService,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        // Générer un secret pour Google Authenticator
        $secret = $authenticatorService->generateSecret();
        $qrCode = $authenticatorService->generateQrCode($user->getEmail(), $secret);

        // Enregistrer le secret
        $user->setGoogleAuthenticatorSecret($secret);
        $user->setGoogleAuthenticatorEnabled(true);
        $entityManager->flush();

        return $this->render('two_factor/setup.html.twig', [
            'qr_code' => $qrCode
        ]);
    }
}