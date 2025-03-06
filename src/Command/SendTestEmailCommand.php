<?php
// src/Command/SendTestEmailCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SendTestEmailCommand extends Command
{
protected static $defaultName = 'app:send-test-email';

private $mailer;

public function __construct(MailerInterface $mailer)
{
parent::__construct();
$this->mailer = $mailer;
}

protected function execute(InputInterface $input, OutputInterface $output): int
{
$email = (new Email())
->from('kayzeurdylan2@gmail.com')
->to('kayzeurdylan@gmail.com')
->subject('Test d\'envoi d\'e-mail')
->text('Ceci est un e-mail de test envoyé depuis Symfony.');

$this->mailer->send($email);

$output->writeln('E-mail envoyé avec succès !');
return Command::SUCCESS;
}
}