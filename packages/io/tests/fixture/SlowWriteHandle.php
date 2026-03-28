<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Fixture;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function strlen;
use function substr;

final class SlowWriteHandle implements IO\WriteHandleInterface, IO\CloseHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    private string $data = '';
    private bool $closed = false;
    private int $budget;

    public function __construct(int $budget)
    {
        $this->budget = $budget;
    }

    public function setMaxBytesPerWrite(int $budget): void
    {
        $this->budget = $budget;
    }

    #[Override]
    public function tryWrite(string $bytes): int
    {
        if ($this->budget === 0) {
            return 0;
        }

        $len = strlen($bytes);
        $n = $len > $this->budget ? $this->budget : $len;
        $this->data .= substr($bytes, 0, $n);
        $this->budget -= $n;

        return $n;
    }

    #[Override]
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        return $this->tryWrite($bytes);
    }

    public function getWrittenData(): string
    {
        return $this->data;
    }

    #[Override]
    public function isClosed(): bool
    {
        return $this->closed;
    }

    #[Override]
    public function close(): void
    {
        $this->closed = true;
    }
}
