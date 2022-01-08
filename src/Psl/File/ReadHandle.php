<?php

declare(strict_types=1);

namespace Psl\File;

use Psl\Filesystem;
use Psl\IO;

final class ReadHandle extends Internal\AbstractHandleWrapper implements ReadHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private ReadHandleInterface $readHandle;

    /**
     * @param non-empty-string $file
     *
     * @throws Exception\NotFoundException If $file does not exist.
     * @throws Exception\NotFileException If $file points to a non-file node on the filesystem.
     * @throws Exception\NotReadableException If $file exists, and is non-readable.
     */
    public function __construct(string $file)
    {
        if (!Filesystem\exists($file)) {
            throw Exception\NotFoundException::for($file);
        }

        if (!Filesystem\is_file($file)) {
            throw Exception\NotFileException::for($file);
        }

        if (!Filesystem\is_readable($file)) {
            throw Exception\NotReadableException::for($file);
        }

        $this->readHandle = Internal\open($file, 'r', read: true, write: false);

        parent::__construct($this->readHandle);
    }

    /**
     * {@inheritDoc}
     */
    public function tryRead(?int $max_bytes = null): string
    {
        return $this->readHandle->tryRead($max_bytes);
    }

    /**
     * {@inheritDoc}
     */
    public function read(?int $max_bytes = null, ?float $timeout = null): string
    {
        return $this->readHandle->read($max_bytes, $timeout);
    }
}
