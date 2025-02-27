<?php

namespace App\Event;
use App\Entity\Community;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class CommunityCreatedEvent extends Event
{
    public const NAME = 'community.created';

    private Community $community;
    private User $user;

    public function __construct(Community $community, User $user)
    {
        $this->community = $community;
        $this->user = $user;
    }

    public function getCommunity(): Community
    {
        return $this->community;

    }

    public function getUser(): User
    {
        return $this->user;
    }
}