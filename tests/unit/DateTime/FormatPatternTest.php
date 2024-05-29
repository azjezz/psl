<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DateTime;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\FormatPattern;

final class FormatPatternTest extends TestCase
{
    use DateTimeTestTrait;

    public function testDefault(): void
    {
        static::assertSame(FormatPattern::Iso8601, FormatPattern::default());
    }
}
