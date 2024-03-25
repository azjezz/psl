<?php

declare(strict_types=1);

namespace Psl\File;

enum WriteMode: string
{
    /**
     * Open the file for writing only; place the file pointer at the beginning of
     * the file.
     *
     * If the file exits, it is not truncated (as with `Truncate`), and the call
     * succeeds (unlike `MustCreate`).
     */
    case OpenOrCreate = 'cb';

    /**
     * Open for writing only; place the file pointer at the beginning of the
     * file and truncate the file to zero length. If the file does not exist,
     * attempt to create it.
     */
    case Truncate = 'wb';

    /**
     * Open for writing only; place the file pointer at the end of the file. If
     * the file does not exist, attempt to create it. In this mode, seeking has
     * no effect, writes are always appended.
     */
    case Append = 'ab';

    /**
     * Create and open for writing only; place the file pointer at the beginning
     * of the file. If the file already exists, the filesystem call will throw an
     * exception. If the file does not exist, attempt to create it.
     */
    case MustCreate = 'xb';
}
