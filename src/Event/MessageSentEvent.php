<?php

namespace App\Event;

use App\Entity\Messages;
use Symfony\Contracts\EventDispatcher\Event;

class MessageSentEvent extends Event
{
    public const NAME = 'message.sent';

    private Messages $message;

    public function __construct(Messages $message)
    {
        $this->message = $message;
    }

    public function getMessage(): Messages
    {
        return $this->message;
    }
}
