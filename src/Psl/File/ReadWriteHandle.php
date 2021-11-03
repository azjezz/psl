<?php

declare(strict_types=1);

namespace Psl\File;

use Psl;
use Psl\Filesystem;
use Psl\IO;

final class ReadWriteHandle extends Internal\AbstractHandleWrapper implements ReadWriteHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;
    use IO\WriteHandleConvenienceMethodsTrait;

    private ReadWriteHandleInterface $readWriteHandle;

    /**
     * @param resource|object $stream
     *
     * @throws IO\Exception\BlockingException If unable to set the stream to non-blocking mode.
     * @throws Psl\Exception\InvariantViolationException If $path points to a non-file node, or it not writeable.
     * @throws Filesystem\Exception\RuntimeException If unable to create $path when it does not exist.
     */
    public function __construct(string $path, WriteMode $write_mode = WriteMode::OPEN_OR_CREATE)
    {
        $is_file = Filesystem\is_file($path);
        Psl\invariant(!Filesystem\exists($path) || $is_file, '$path points to a non-file node.');

        $open_or_create = $write_mode === WriteMode::OPEN_OR_CREATE;
        $must_create = $write_mode === WriteMode::MUST_CREATE;
        if ($must_create && $is_file) {
            Psl\invariant_violation('$path already exists.');
        }

        $creating = $open_or_create || $must_create;
        if (!$creating && !$is_file) {
            Psl\invariant_violation('$path does not exist.');
        }

        if ((!$creating || ($open_or_create && $is_file)) && !Filesystem\is_writable($path)) {
            Psl\invariant_violation('$path is not writable.');
        }

        if ($creating && !$is_file) {
            Filesystem\create_file($path);
        }

        /**
         * @psalm-suppress UndefinedPropertyFetch
         * @psalm-suppress MixedArgument
         */
        $this->readWriteHandle = Internal\open($path, 'r' . ((string) $write_mode->value) . '+', read: true, write: false);

        parent::__construct($this->readWriteHandle);
    }

    /**
     * {@inheritDoc}
     */
    public function readImmediately(?int $max_bytes = null): string
    {
        return $this->readWriteHandle->readImmediately($max_bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function read(?int $max_bytes = null, ?int $timeout_ms = null): string
    {
        return $this->readWriteHandle->read($max_bytes, $timeout_ms);
    }

    /**
     * {@inheritDoc}
     */
    public function writeImmediately(string $bytes): int
    {
        return $this->readWriteHandle->writeImmediately($bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $bytes, ?int $timeout_ms = null): int
    {
        return $this->readWriteHandle->write($bytes, $timeout_ms);
    }
}
