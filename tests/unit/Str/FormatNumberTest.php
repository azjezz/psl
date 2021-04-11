<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class FormatNumberTest extends TestCase
{
    public function testFormatNumber(): void
    {
        static::assertSame('487 891,49', Str\format_number(487891.4879, 2, ',', ' '));
    }
}
