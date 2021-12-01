<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Exception;
use Psl;
use Psl\Async;

final class SeriesTest extends TestCase
{
    public function testSeries(): void
    {
        $spy = new Psl\Ref('');

        Async\series([
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

        static::assertSame('123', $spy->value);
    }

    public function testSeriesThrowsForTheFirstAndDoesNotCallTheRest(): void
    {
        Async\Scheduler::run();

        $spy = new Psl\Ref('');

        try {
            Async\series([
                static function (): void {
                    Async\sleep(0.003);

                    throw new Exception('foo');
                },
                static function () use ($spy): void {
                    $spy->value = 'thrown';

                    throw new Exception('bar');
                },
            ]);
        } catch (Exception $exception) {
            static::assertSame('foo', $exception->getMessage());
        }

        // wait for all callbacks in the event loop to finish.
        Async\Scheduler::run();

        static::assertSame('', $spy->value);
    }
}
