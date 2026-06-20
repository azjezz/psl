<?php

declare(strict_types=1);

namespace Psl\Observer;

/**
 * @api
 */
interface ObserverInterface<T : SubjectInterface>
{
    /**
     * Receive an update from a subject.
     *
     * @param T $subject
     */
    public function update(SubjectInterface $subject): void;
}
