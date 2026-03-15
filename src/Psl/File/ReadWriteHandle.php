<?php

declare(strict_types=1);

namespace Psl\File;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Filesystem;
use Psl\IO;
use Psl\Str;

final class ReadWriteHandle extends Internal\AbstractHandleWrapper implements WriteHandleInterface, ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;
    use IO\WriteHandleConvenienceMethodsTrait;

    private ReadHandleInterface&WriteHandleInterface $readWriteHandle;

    /**
     * @param non-empty-string $file
     *
     * @throws Exception\NotFileException If $file points to a non-file node on the filesystem.
     * @throws Exception\AlreadyCreatedException If $file is already created, and $writeMode is {@see WriteMode::MUST_CREATE}.
     * @throws Exception\NotFoundException If $file does not exist, and $writeMode is {@see WriteMode::TRUNCATE} or {@see WriteMode::APPEND}.
     * @throws Exception\NotWritableException If $file exists, and is non-writable
     * @throws Exception\NotReadableException If $file exists, and is non-readable.
     * @throws Exception\RuntimeException If unable to create the $file if it does not exist.
     */
    public function __construct(string $file, WriteMode $writeMode = WriteMode::OpenOrCreate)
    {
        $isFile = Filesystem\is_file($file);
        if (!$isFile && Filesystem\exists($file)) {
            throw Exception\NotFileException::for($file);
        }

        $mustCreate = $writeMode === WriteMode::MustCreate;
        if ($mustCreate && $isFile) {
            throw Exception\AlreadyCreatedException::for($file);
        }

        if ($isFile) {
            if (!Filesystem\is_writable($file)) {
                throw Exception\NotWritableException::for($file);
            }

            if (!Filesystem\is_readable($file)) {
                throw Exception\NotReadableException::for($file);
            }
        }

        if (!$isFile) {
            try {
                $directory = Filesystem\create_directory_for_file($file);
                if (!Filesystem\is_writable($directory)) {
                    throw Exception\NotWritableException::for($file);
                }

                if (!Filesystem\is_readable($directory)) {
                    throw Exception\NotReadableException::for($file);
                }
            } catch (Filesystem\Exception\RuntimeException $previous) {
                throw new Exception\RuntimeException(
                    Str\format('Failed to create the directory for file "%s".', $file),
                    previous: $previous,
                );
            }
        }

        $this->readWriteHandle = Internal\open($file, $writeMode->value . 'r+', read: true, write: true);

        parent::__construct($this->readWriteHandle);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function reachedEndOfDataSource(): bool
    {
        return $this->readWriteHandle->reachedEndOfDataSource();
    }

    /**
     * @param ?positive-int $maxBytes the maximum number of bytes to read
     *
     * @inheritDoc
     */
    #[Override]
    public function tryRead(null|int $maxBytes = null): string
    {
        return $this->readWriteHandle->tryRead($maxBytes);
    }

    /**
     * @param ?positive-int $maxBytes the maximum number of bytes to read
     *
     * @inheritDoc
     */
    #[Override]
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        return $this->readWriteHandle->read($maxBytes, $cancellation);
    }

    /**
     * @return int<0, max>
     *
     * @inheritDoc
     */
    #[Override]
    public function tryWrite(string $bytes): int
    {
        return $this->readWriteHandle->tryWrite($bytes);
    }

    /**
     * @return int<0, max>
     *
     * @inheritDoc
     */
    #[Override]
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        return $this->readWriteHandle->write($bytes, $cancellation);
    }
}
