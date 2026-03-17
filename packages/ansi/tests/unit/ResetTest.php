<?php

declare(strict_types=1);

namespace Psl\Ansi\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;
use Psl\Ansi\ControlSequenceIntroducerKind;

final class ResetTest extends TestCase
{
    public function testReset(): void
    {
        $sequence = Ansi\reset();

        static::assertSame('0', $sequence->parameters);
        static::assertSame(ControlSequenceIntroducerKind::SelectGraphicRendition, $sequence->kind);
        static::assertSame("\e[0m", $sequence->toString());
    }
}
