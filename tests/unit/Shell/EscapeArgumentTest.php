<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Shell;

use Psl\Shell;
use Psl\Tests\Unit\IOTestCase;

final class EscapeArgumentTest extends IOTestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEscapeArgument(string $argument): void
    {
        $output = Shell\execute(PHP_BINARY, ['-r', 'echo $argv[1];', $argument]);

        static::assertSame($argument, $output);
    }

    /**
     * @return iterable<array{0: string}>
     */
    public function provideData(): iterable
    {
        yield ['a"b%c%'];
        yield ['a"b^c^'];
        yield ["a\nb'c"];
        yield ['a^b c!'];
        yield ["a!b\tc"];
        yield ["look up ^"];
        yield ['a\\\\"\\"'];
        yield ['éÉèÈàÀöä'];
        yield ['1'];
        yield ['1.1'];
        yield ['1%2'];
        yield ["Hey there,\nHow are you doing!"];
        yield [''];
    }
}
