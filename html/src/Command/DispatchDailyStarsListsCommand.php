<?php

namespace App\Command;

use App\Message\DailyStarsLists;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DispatchDailyStarsListsCommand extends Command
{
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        parent::__construct();

        $this->messageBus = $messageBus;
    }

    protected function configure()
    {
        $this->setName('app:dispatch-daily-stars-lists');
        $this->setDescription('Dispatches the DailyStarsLists message to the message bus.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Dispatching DailyStarsLists message...');
        
        // Create the message
        $message = new DailyStarsLists();

        // Dispatch the message
        $this->messageBus->dispatch($message);

        $output->writeln('Message dispatched!');

        return Command::SUCCESS;
    }
}
