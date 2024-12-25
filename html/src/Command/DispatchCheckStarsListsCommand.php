<?php

namespace App\Command;

use App\Message\CheckStarsLists;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DispatchCheckStarsListsCommand extends Command
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        parent::__construct();

        $this->messageBus = $messageBus;
    }

    protected function configure()
    {
        $this->setName('app:dispatch-check-stars-lists');
        $this->setDescription('Dispatches the CheckStarsLists message to the message bus.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Dispatching CheckStarsLists message...');
        
        // Create the message
        $message = new CheckStarsLists();

        // Dispatch the message
        $this->messageBus->dispatch($message);

        $output->writeln('Message dispatched!');

        return Command::SUCCESS;
    }
}
