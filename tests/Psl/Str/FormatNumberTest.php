<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class FormatNumberTest extends TestCase
{
    public function testFormatNumber(): void
    {
        self::assertSame('487 891,49', Str\format_number(487891.4879, 2, ',', ' '));
    }
}
