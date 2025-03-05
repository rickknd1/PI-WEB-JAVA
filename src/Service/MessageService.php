<?php


namespace App\Service;

use App\Entity\Messages;
use Doctrine\ORM\EntityManagerInterface;

class MessageService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function handleMessage(Messages $message): void
    {
        $this->entityManager->persist($message);
        $this->entityManager->flush();
    }
}
