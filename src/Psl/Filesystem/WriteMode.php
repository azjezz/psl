<?php

declare(strict_types=1);

namespace Psl\Filesystem;

/**
 * @TODO(azjezz): make this an enum.
 */
final class WriteMode
{
    /**
     * Open the file for writing only; place the file pointer at the beginning of
     * the file.
     *
     * If the file exits, it is not truncated (as with `TRUNCATE`), and the call
     * succeeds (unlike `EXCLUSIVE_CREATE`).
     */
    public const OPEN_OR_CREATE = 'cb';

    /**
     * Open for writing only; place the file pointer at the beginning of the
     * file and truncate the file to zero length. If the file does not exist,
     * attempt to create it.
     */
    public const TRUNCATE = 'wb';

    /**
     * Open for writing only; place the file pointer at the end of the file. If
     * the file does not exist, attempt to create it. In this mode, seeking has
     * no effect, writes are always appended.
     */
    public const APPEND = 'ab';

    /**
     * Create and open for writing only; place the file pointer at the beginning
     * of the file. If the file already exists, the filesystem call will throw an
     * exception. If the file does not exist, attempt to create it.
     */
    public const MUST_CREATE = 'xb';

    private function __construct()
    {
    }
}
