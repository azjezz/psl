<?php

declare(strict_types=1);

namespace Psl\File;

use Psl\Filesystem;
use Psl\IO;
use Psl\Str;

final class ReadWriteHandle extends Internal\AbstractHandleWrapper implements ReadWriteHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;
    use IO\WriteHandleConvenienceMethodsTrait;

    private ReadWriteHandleInterface $readWriteHandle;

    /**
     * @param non-empty-string $file
     *
     * @throws Exception\NotFileException If $file points to a non-file node on the filesystem.
     * @throws Exception\AlreadyCreatedException If $file is already created, and $write_mode is {@see WriteMode::MUST_CREATE}.
     * @throws Exception\NotFoundException If $file does not exist, and $write_mode is {@see WriteMode::TRUNCATE} or {@see WriteMode::APPEND}.
     * @throws Exception\NotWritableException If $file exists, and is non-writable
     * @throws Exception\NotReadableException If $file exists, and is non-readable.
     * @throws Exception\RuntimeException If unable to create the $file if it does not exist.
     */
    public function __construct(string $file, WriteMode $write_mode = WriteMode::OPEN_OR_CREATE)
    {
        $is_file = Filesystem\is_file($file);
        if (!$is_file && Filesystem\exists($file)) {
            throw Exception\NotFileException::for($file);
        }

        $must_create = $write_mode === WriteMode::MUST_CREATE;
        if ($must_create && $is_file) {
            throw Exception\AlreadyCreatedException::for($file);
        }

        if ($is_file) {
            if (!Filesystem\is_writable($file)) {
                throw Exception\NotWritableException::for($file);
            }

            if (!Filesystem\is_readable($file)) {
                throw Exception\NotReadableException::for($file);
            }
        }

        if (!$is_file) {
            try {
                $directory = Filesystem\create_directory_for_file($file);
                if (!Filesystem\is_writable($directory)) {
                    throw Exception\NotWritableException::for($file);
                }

                if (!Filesystem\is_readable($directory)) {
                    throw Exception\NotReadableException::for($file);
                }
            } catch (Filesystem\Exception\RuntimeException $previous) {
                throw new Exception\RuntimeException(Str\format('Failed to create the directory for file "%s".', $file), previous: $previous);
            }
        }

        $this->readWriteHandle = Internal\open($file, ($write_mode->value) . 'r+', read: true, write: true);

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
