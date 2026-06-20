<?php

declare(strict_types=1);

namespace Psl\Observer;

/**
 * @api
 */
interface ObserverInterface<T: SubjectInterface>
{
    /**
     * Receive an update from a subject.
     */
    public function update(T $subject): void;
}
