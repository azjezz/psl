<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

use function array_values;

final class ValuesTest extends TestCase
{
    public function testValues(): void
    {
        $array = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ];

        $values = Arr\values($array);

        static::assertSameSize($values, $array);
        static::assertSame($values, array_values($array));

        foreach ($array as $value) {
            static::assertTrue(Arr\contains($values, $value));
        }
    }
}
