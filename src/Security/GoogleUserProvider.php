<?php
namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GoogleUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository
    ) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userRepository->findOneBy(['email' => $identifier]);

        if (!$user) {
            throw new UserNotFoundException('Utilisateur non trouvé.');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }

    // Implémentation de OAuthAwareUserProviderInterface
    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $googleUser = $response;

        // Rechercher un utilisateur existant par googleId ou email
        $user = $this->userRepository->findOneBy(['googleId' => $googleUser->getUsername()])
            ?? $this->userRepository->findOneBy(['email' => $googleUser->getEmail()]);

        // Si aucun utilisateur existant, créer un nouvel utilisateur
        if (!$user) {
            $user = new User();
            $user->setEmail($googleUser->getEmail());
            $user->setGoogleId($googleUser->getUsername());

            // Définir le username
            $user->setUsername($googleUser->getRealName() ?? $googleUser->getEmail());

            // Extraire le nom et prénom
            $nameParts = explode(' ', $googleUser->getRealName() ?? '');
            $user->setFirstname($nameParts[0] ?? '');
            $user->setName(count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '');

            // Définir un rôle par défaut
            $user->setRole('ROLE_USER');

            // Vérification automatique
            $user->setIsVerified(true);

            // Persister l'utilisateur
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        // Vérifier si le compte est banni
        if ($user->isBanned()) {
            throw new UserNotFoundException('Votre compte a été banni.');
        }

        return $user;
    }
}