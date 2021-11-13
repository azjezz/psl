<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Async;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Async;
use Psl\IO;

final class AwaitReadableTest extends TestCase
{
    public function testAwaitReadable(): void
    {
        [$read, $write] = IO\pipe();

        $ref = new Psl\Ref('');
        $handles = [
            Async\run(static function () use ($ref, $read) {
                $ref->value .= '[read:waiting]';

                $read_socket = $read->getStream();
                Async\await_readable($read_socket);

                $ref->value .= '[read:ready]';
            }),
            Async\run(static function () use ($ref, $write) {
                $ref->value .= '[write:sleep]';

                Async\sleep(0.001);

                $write->writeImmediately("hello");

                $ref->value .= '[write:done]';
            }),
        ];

        Async\all($handles);

        static::assertSame('[read:waiting][write:sleep][write:done][read:ready]', $ref->value);

        $write->close();
        $read->close();
    }
}
