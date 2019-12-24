<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

class ValuesTest extends TestCase
{
    public function testValues(): void
    {
        $array = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ];

        $values = Arr\values($array);

        self::assertSameSize($values, $array);
        self::assertSame($values, \array_values($array));

        foreach ($array as $value) {
            self::assertTrue(Arr\contains($values, $value));
        }
    }
}
