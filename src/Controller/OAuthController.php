<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Security\GoogleUserProvider;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\Routing\Annotation\Route;
use HWI\Bundle\OAuthBundle\OAuth\ResourceOwner\GoogleResourceOwner;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use HWI\Bundle\OAuthBundle\Security\Http\ResourceOwnerMap;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;


class OAuthController extends AbstractController
{

    public function googleConnect(
        ClientRegistry $clientRegistry,
        GoogleUserProvider $googleUserProvider
    ) {
        $client = $clientRegistry->getClient('google');
        $accessToken = $client->getAccessToken();

        /** @var GoogleUser $googleUser */
        $googleUser = $client->fetchUserFromToken($accessToken);

        $user = $googleUserProvider->loadUserByGoogleUser($googleUser);

        // Connecter l'utilisateur
        return $this->redirectToRoute('home');
    }
}