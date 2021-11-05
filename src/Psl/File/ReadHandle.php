<?php

declare(strict_types=1);

namespace Psl\File;

use Psl;
use Psl\Filesystem;
use Psl\IO;

final class ReadHandle extends Internal\AbstractHandleWrapper implements ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private ReadHandleInterface $readHandle;

    /**
     * @param resource|object $stream
     *
     * @throws IO\Exception\BlockingException If unable to set the stream to non-blocking mode.
     * @throws Psl\Exception\InvariantViolationException If $path does not point to a file, or is not readable.
     */
    public function __construct(string $path)
    {
        Psl\invariant(Filesystem\is_file($path), 'File "%s" is not a file.', $path);
        Psl\invariant(Filesystem\is_readable($path), 'File "%s" is not readable.', $path);

        $this->readHandle = Internal\open($path, 'r', read: true, write: false);

        parent::__construct($this->readHandle);
    }

    /**
     * {@inheritDoc}
     */
    public function readImmediately(?int $max_bytes = null): string
    {
        return $this->readHandle->readImmediately($max_bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function read(?int $max_bytes = null, ?float $timeout = null): string
    {
        return $this->readHandle->read($max_bytes, $timeout);
    }
}
