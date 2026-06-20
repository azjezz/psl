<?php

declare(strict_types=1);

namespace Psl\Observer;

/**
 * @api
 */
interface SubjectInterface
{
    /**
     * Subscribe to the given observer.
     *
     * @param ObserverInterface<static> $observer
     */
    public function subscribe(ObserverInterface<static> $observer): void;

    /**
     * Unsubscribe from the given observer.
     *
     * @param ObserverInterface<static> $observer
     */
    public function unsubscribe(ObserverInterface<static> $observer): void;

    /**
     * Notify observers of an update.
     */
    public function notify(): void;
}
