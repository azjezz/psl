<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;
use Psl\Exception\InvariantViolationException;

final class AllTest extends TestCase
{
    public function testAll(): void
    {
        $awaitables = [
            'a' => Async\run(static function (): string {
                Async\sleep(0.003);

                return 'a';
            }),
            'b' => Async\run(static function (): string {
                Async\sleep(0.001);

                return 'b';
            }),
            'c' => Async\run(static function (): string {
                Async\sleep(0.01);

                return 'c';
            }),
        ];

        $results = Async\all($awaitables);

        static::assertSame(['a' => 'a', 'b' => 'b', 'c' => 'c'], $results);
    }

    public function testAllForwardsException(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('a');

        Async\all([
            Async\run(static function (): string {
                Async\sleep(0.01);

                throw new InvariantViolationException('a');
            }),
            Async\run(static function (): string {
                Async\sleep(0.02);

                throw new InvariantViolationException('b');
            }),
            Async\run(static function (): string {
                Async\sleep(0.03);

                return 'c';
            }),
            Async\run(static function (): string {
                Async\sleep(0.005);

                Async\later();

                Async\sleep(0.005);

                return 'c';
            }),
        ]);
    }

    public function testAllAwaitablesAreCompleted(): void
    {
        $ref = new Psl\Ref('');

        try {
            Async\all([
                Async\run(static function () use ($ref): void {
                    $ref->value .= 'a';

                    throw new InvariantViolationException('a');
                }),
                Async\run(static function () use ($ref): void {
                    Async\sleep(0.02);

                    $ref->value .= 'b';

                    throw new InvariantViolationException('b');
                }),
                Async\run(static function () use ($ref): void {
                    Async\sleep(0.05);

                    $ref->value .= 'c';
                }),
                Async\run(static function () use ($ref): void {
                    Async\sleep(0.00005);

                    Async\later();

                    Async\sleep(0.00005);

                    $ref->value .= 'd';
                }),
            ]);
        } catch (InvariantViolationException) {
            $this->addToAssertionCount(1);
        }

        static::assertSame('adbc', $ref->value);
    }
}
