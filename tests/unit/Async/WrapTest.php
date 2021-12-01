<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Result;

final class WrapTest extends TestCase
{
    public function testWrap(): void
    {
        $one = Async\wrap(static function (): void {
            Async\sleep(0.0001);

            throw new Exception('failure');
        });

        $two = Async\wrap(static function (): string {
            return 'success';
        });

        static::assertInstanceOf(Result\Failure::class, $one);
        static::assertInstanceOf(Result\Success::class, $two);

        static::assertSame('failure', $one->getException()->getMessage());
        static::assertSame('success', $two->getResult());
    }
}
