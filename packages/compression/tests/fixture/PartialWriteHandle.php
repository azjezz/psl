<?php

declare(strict_types=1);

namespace Psl\Compression\Tests\Fixture;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function min;
use function strlen;
use function substr;

final class PartialWriteHandle implements IO\WriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private string $buffer = '';

    /**
     * @param positive-int $maxBytesPerWrite
     */
    public function __construct(
        private readonly int $maxBytesPerWrite,
    ) {}

    #[Override]
    public function tryWrite(string $bytes): int
    {
        $length = min(strlen($bytes), $this->maxBytesPerWrite);
        $this->buffer .= substr($bytes, 0, $length);

        return $length;
    }

    #[Override]
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        return $this->tryWrite($bytes);
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }
}
