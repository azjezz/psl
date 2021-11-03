<?php

declare(strict_types=1);

namespace Psl\File;

enum LockType
{
    /**
     * Any number of processes may have a shared lock simultaneously. It is
     * commonly called a reader lock. The creation of a Lock will block until
     * the lock is acquired.
     */
    case SHARED;

    /**
     * Only a single process may possess an exclusive lock to a given file at a
     * time. The creation of a Lock will block until the lock is acquired.
     */
    case EXCLUSIVE;
}
