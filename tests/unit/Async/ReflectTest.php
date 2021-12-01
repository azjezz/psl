<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use Exception;
use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Result;

final class ReflectTest extends TestCase
{
    public function testReflectParallel(): void
    {
        [$one, $two] = Async\parallel([
            Async\reflect(static function (): void {
                Async\sleep(0.0001);

                throw new Exception('failure');
            }),
            Async\reflect(static function (): string {
                return 'success';
            }),
        ]);

        static::assertInstanceOf(Result\Failure::class, $one);
        static::assertInstanceOf(Result\Success::class, $two);

        static::assertSame('failure', $one->getException()->getMessage());
        static::assertSame('success', $two->getResult());
    }
}
