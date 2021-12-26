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
     * @param non-empty-string $path
     *
     * @throws Psl\Exception\InvariantViolationException If $path points to a non-file node, or it not writeable.
     * @throws Filesystem\Exception\RuntimeException If unable to create $path when it does not exist.
     */
    public function __construct(string $path, WriteMode $write_mode = WriteMode::OPEN_OR_CREATE)
    {
        $is_file = Filesystem\is_file($path);
        Psl\invariant(!Filesystem\exists($path) || $is_file, 'File "%s" is not a file.', $path);

        $open_or_create = $write_mode === WriteMode::OPEN_OR_CREATE;
        $must_create = $write_mode === WriteMode::MUST_CREATE;
        if ($must_create && $is_file) {
            Psl\invariant_violation('File "%s" already exists.', $path);
        }

        $creating = $open_or_create || $must_create;
        if (!$creating && !$is_file) {
            Psl\invariant_violation('File "%s" does not exist.', $path);
        }

        if ((!$creating || ($open_or_create && $is_file)) && !Filesystem\is_writable($path)) {
            Psl\invariant_violation('File "%s" is not writable.', $path);
        }

        if ($creating && !$is_file) {
            Filesystem\create_file($path);
        }

        $this->readWriteHandle = Internal\open($path, 'r' . ($write_mode->value) . '+', read: true, write: true);

        parent::__construct($this->readWriteHandle);
    }

    /**
     * {@inheritDoc}
     */
    public function tryRead(?int $max_bytes = null): string
    {
        return $this->readWriteHandle->tryRead($max_bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function read(?int $max_bytes = null, ?float $timeout = null): string
    {
        return $this->readWriteHandle->read($max_bytes, $timeout);
    }

    /**
     * {@inheritDoc}
     */
    public function tryWrite(string $bytes): int
    {
        return $this->readWriteHandle->tryWrite($bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $bytes, ?float $timeout = null): int
    {
        return $this->readWriteHandle->write($bytes, $timeout);
    }
}
