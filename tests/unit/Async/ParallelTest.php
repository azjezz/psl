<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Exception;
use Psl;
use Psl\Async;

final class ParallelTest extends TestCase
{
    public function testParallel(): void
    {
        $spy = new Psl\Ref('');

        Async\parallel([
            static function () use ($spy): void {
                Async\sleep(0.003);

                $spy->value .= '1';
            },
            static function () use ($spy): void {
                Async\sleep(0.001);

                $spy->value .= '2';
            },
            static function () use ($spy): void {
                Async\sleep(0.001);

                $spy->value .= '3';
            },
        ]);

        static::assertSame('231', $spy->value);
    }

    public function testParallelThrowsForTheFirstAndDoesNotCallTheRest(): void
    {
        Async\Scheduler::run();

        $spy = new Psl\Ref('');

        try {
            Async\parallel([
                static function (): void {
                    Async\sleep(0.003);

                    throw new Exception('foo');
                },
                static function () use ($spy): void {
                    Async\sleep(0.004);

                    $spy->value = 'thrown';

                    throw new Exception('bar');
                },
            ]);
        } catch (Exception $exception) {
            static::assertSame('foo', $exception->getMessage());
        }

        // wait for all callbacks in the event loop to finish.
        // including the second callback given to parallel.
        // this ensures that even after it finishes, the exception will be ignored.
        Async\Scheduler::run();

        // the second callback was finished, but it didn't throw the exception ( which is exactly what we want )
        static::assertSame('thrown', $spy->value);
    }
}
