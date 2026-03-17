<?php

declare(strict_types=1);

namespace Psl\Observer;

/**
 * @template T of SubjectInterface
 */
interface ObserverInterface
{
    /**
     * Receive an update from a subject.
     *
     * @param T $subject
     */
    public function update(SubjectInterface $subject): void;
}
