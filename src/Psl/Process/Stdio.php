<?php

declare(strict_types=1);

namespace Psl\Process;

use Psl\IO;

/**
 * Describes what to do with a standard I/O stream for a child process.
 */
final readonly class Stdio
{
    private const int TYPE_PIPED = 0;
    private const int TYPE_INHERIT = 1;
    private const int TYPE_NULL = 2;
    private const int TYPE_HANDLE = 3;

    private function __construct(
        private int $type,
        private null|IO\StreamHandleInterface $handle = null,
    ) {}

    /**
     * A new pipe should be arranged to connect the parent and child processes.
     */
    public static function piped(): self
    {
        return new self(self::TYPE_PIPED);
    }

    /**
     * The child inherits from the corresponding parent descriptor.
     */
    public static function inherit(): self
    {
        return new self(self::TYPE_INHERIT);
    }

    /**
     * This stream will be ignored. This is the equivalent of attaching the stream to /dev/null.
     */
    public static function null(): self
    {
        return new self(self::TYPE_NULL);
    }

    /**
     * The child will use the given stream handle.
     */
    public static function fromStreamHandle(IO\StreamHandleInterface $handle): self
    {
        return new self(self::TYPE_HANDLE, $handle);
    }

    /**
     * @internal
     */
    public function isPiped(): bool
    {
        return self::TYPE_PIPED === $this->type;
    }

    /**
     * @internal
     */
    public function isInherit(): bool
    {
        return self::TYPE_INHERIT === $this->type;
    }

    /**
     * @internal
     */
    public function isNull(): bool
    {
        return self::TYPE_NULL === $this->type;
    }

    /**
     * @internal
     */
    public function isHandle(): bool
    {
        return self::TYPE_HANDLE === $this->type;
    }

    /**
     * @internal
     */
    public function getHandle(): null|IO\StreamHandleInterface
    {
        return $this->handle;
    }
}
