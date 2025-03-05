<?php


namespace App\EventListener;

use App\Event\MessageSentEvent;
use App\Service\MessageService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class MessageSentListener
{
    private MessageService $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    #[AsEventListener(event: MessageSentEvent::NAME)]
    public function onMessageSent(MessageSentEvent $event)
    {
        $message = $event->getMessage();
        $this->messageService->handleMessage($message);
    }
}
