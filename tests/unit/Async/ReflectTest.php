<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime;
use Psl\Result;

final class ReflectTest extends TestCase
{
    public function testReflectParallel(): void
    {
        [$one, $two] = Async\concurrently([
            Result\reflect(static function (): void {
                Async\sleep(DateTime\Duration::milliseconds(1));

                throw new Exception('failure');
            }),
            Result\reflect(static function (): string {
                return 'success';
            }),
        ]);

        static::assertInstanceOf(Result\Failure::class, $one);
        static::assertInstanceOf(Result\Success::class, $two);

        static::assertSame('failure', $one->getThrowable()->getMessage());
        static::assertSame('success', $two->getResult());
    }
}
