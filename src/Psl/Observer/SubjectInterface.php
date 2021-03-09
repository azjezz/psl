<?php

declare(strict_types=1);

namespace Psl\Observer;

interface SubjectInterface
{
    /**
     * Subscribe to the given observer.
     *
     * @param ObserverInterface<static> $observer
     */
    public function subscribe(ObserverInterface $observer): void;

    /**
     * Unsubscribe from the given observer.
     *
     * @psam-param ObserverInterface<static> $observer
     */
    public function unsubscribe(ObserverInterface $observer): void;

    /**
     * Notify observers of an update.
     */
    public function notify(): void;
}
