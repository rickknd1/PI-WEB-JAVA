<?php

namespace App\Service;

use App\Entity\MembreComunity;
use App\Repository\CommunityRepository;
use App\Repository\MembreComunityRepository;
use App\Repository\UserRepository;

class CommService
{
    public function __construct(
        private readonly MembreComunityRepository $membreComunityRepository,
    ){
    }

    /**
     * @return MembreComunity[]
     */
    public function findUserComm(): array
    {
        return $this->membreComunityRepository->findAll();
    }
}