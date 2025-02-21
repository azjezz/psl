<?php

declare(strict_types=1);

namespace Psl\File;

use Psl\DateTime\Duration;
use Psl\Filesystem;
use Psl\IO;
use Psl\Str;

final class WriteHandle extends Internal\AbstractHandleWrapper implements WriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private WriteHandleInterface $writeHandle;

    /**
     * @param non-empty-string $file
     *
     * @throws Exception\NotFileException If $file points to a non-file node on the filesystem.
     * @throws Exception\AlreadyCreatedException If $file is already created, and $write_mode is {@see WriteMode::MUST_CREATE}.
     * @throws Exception\NotFoundException If $file does not exist, and $write_mode is {@see WriteMode::TRUNCATE} or {@see WriteMode::APPEND}.
     * @throws Exception\NotWritableException If $file is non-writable.
     * @throws Exception\RuntimeException If unable to create the $file if it does not exist.
     */
    public function __construct(string $file, WriteMode $write_mode = WriteMode::OpenOrCreate)
    {
        $is_file = Filesystem\is_file($file);
        if (!$is_file && Filesystem\exists($file)) {
            throw Exception\NotFileException::for($file);
        }

        $must_create = $write_mode === WriteMode::MustCreate;
        if ($must_create && $is_file) {
            throw Exception\AlreadyCreatedException::for($file);
        }

        if ($is_file && !Filesystem\is_writable($file)) {
            throw Exception\NotWritableException::for($file);
        }

        if (!$is_file) {
            try {
                $directory = Filesystem\create_directory_for_file($file);
                if (!Filesystem\is_writable($directory)) {
                    throw Exception\NotWritableException::for($file);
                }
            } catch (Filesystem\Exception\RuntimeException $previous) {
                throw new Exception\RuntimeException(
                    Str\format('Failed to create the directory for file "%s".', $file),
                    previous: $previous,
                );
            }
        }

        $this->writeHandle = Internal\open($file, $write_mode->value, read: false, write: true);

        parent::__construct($this->writeHandle);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function tryWrite(string $bytes): int
    {
        return $this->writeHandle->tryWrite($bytes);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function write(string $bytes, null|Duration $timeout = null): int
    {
        return $this->writeHandle->write($bytes, $timeout);
    }
}
