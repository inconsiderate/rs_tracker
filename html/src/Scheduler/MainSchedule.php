<?php

namespace App\Scheduler;

use Symfony\Component\Scheduler\Attribute\AsSchedule;
use App\Message\CheckStarsLists;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsSchedule('listCheck')]
final class MainSchedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
    ) {
    }

    public function getSchedule(): Schedule
    {
        if (_SERVER['APP_ENV'] == 'dev') {
            $freq = '15 seconds';
        } else {
            $freq = '15 minutes';
        }

        return (new Schedule())
            ->add(RecurringMessage::every($freq, new CheckStarsLists()));
    }
}
