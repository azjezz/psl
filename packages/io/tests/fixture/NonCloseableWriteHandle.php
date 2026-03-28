<?php

declare(strict_types=1);

namespace Psl\IO\Tests\Fixture;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\IO;

use function strlen;

final class NonCloseableWriteHandle implements IO\WriteHandleInterface
{
    use IO\WriteHandleConvenienceMethodsTrait;

    #[Override]
    public function tryWrite(string $bytes): int
    {
        return strlen($bytes);
    }

    #[Override]
    public function write(string $bytes, CancellationTokenInterface $cancellation = new NullCancellationToken()): int
    {
        return strlen($bytes);
    }
}
