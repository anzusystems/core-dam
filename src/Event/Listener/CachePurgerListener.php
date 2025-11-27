<?php

declare(strict_types=1);

namespace App\Event\Listener;

use App\Cache\CacheCdnPurger;
use App\Cache\CachePurger;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;

#[AsEventListener(event: KernelEvents::TERMINATE, method: 'onKernelTerminate')]
#[AsEventListener(event: ConsoleEvents::TERMINATE, method: 'onConsoleTerminate')]
#[AsEventListener(event: WorkerMessageHandledEvent::class, method: 'onWorkerMessageHandled')]
final readonly class CachePurgerListener
{
    public function __construct(
        private CachePurger $cachePurger,
        private CacheCdnPurger $cacheCdnPurger,
    ) {
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $this->cachePurger->purge();
        $this->cacheCdnPurger->purge();
    }

    public function onConsoleTerminate(ConsoleEvent $event): void
    {
        $this->cachePurger->purge();
        $this->cacheCdnPurger->purge();
    }

    public function onWorkerMessageHandled(WorkerMessageHandledEvent $event): void
    {
        $this->cachePurger->purge();
        $this->cacheCdnPurger->purge();
    }
}
