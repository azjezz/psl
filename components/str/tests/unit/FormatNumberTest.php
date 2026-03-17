<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class FormatNumberTest extends TestCase
{
    public function testFormatNumber(): void
    {
        static::assertSame('487 891,49', Str\format_number(487_891.487_9, 2, ',', ' '));
    }
}
