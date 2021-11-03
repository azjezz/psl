<?php

declare(strict_types=1);

namespace Psl\File;

use Psl;
use Psl\Filesystem;
use Psl\IO;

final class WriteHandle extends Internal\AbstractHandleWrapper implements WriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private WriteHandleInterface $writeHandle;

    /**
     * @param resource|object $stream
     *
     * @throws IO\Exception\BlockingException If unable to set the stream to non-blocking mode.
     * @throws Psl\Exception\InvariantViolationException If $filename points to a non-file node, or it not writeable.
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

        /**
         * @psalm-suppress UndefinedPropertyFetch
         * @psalm-suppress MixedArgument
         */
        $this->writeHandle = Internal\open($path, (string) $write_mode->value, read: false, write: true);

        parent::__construct($this->writeHandle);
    }

    /**
     * {@inheritDoc}
     */
    public function writeImmediately(string $bytes): int
    {
        return $this->writeHandle->writeImmediately($bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $bytes, ?int $timeout_ms = null): int
    {
        return $this->writeHandle->write($bytes, $timeout_ms);
    }
}
