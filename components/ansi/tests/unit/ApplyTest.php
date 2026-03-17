<?php

declare(strict_types=1);

namespace Psl\Ansi\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Ansi;
use Psl\Ansi\ControlSequenceIntroducer;
use Psl\Ansi\ControlSequenceIntroducerKind;
use Psl\Ansi\Exception;
use Psl\Ansi\Style;

final class ApplyTest extends TestCase
{
    public function testSingleStyle(): void
    {
        $result = Ansi\apply('hello', Style\bold());

        static::assertSame("\e[1mhello\e[0m", $result);
    }

    public function testMultipleStyles(): void
    {
        $result = Ansi\apply('hello', Style\bold(), Ansi\foreground(Ansi\Color\red()));

        static::assertSame("\e[1;31mhello\e[0m", $result);
    }

    public function testEmptySequences(): void
    {
        $result = Ansi\apply('hello');

        static::assertSame('hello', $result);
    }

    public function testNonSgrSequenceThrows(): void
    {
        $this->expectException(Exception\InvalidArgumentException::class);

        Ansi\apply('hello', Ansi\Cursor\up(2));
    }

    public function testMixedSgrAndNonSgrThrows(): void
    {
        $sgr = new ControlSequenceIntroducer('1', ControlSequenceIntroducerKind::SelectGraphicRendition);
        $cursor = new ControlSequenceIntroducer('5', ControlSequenceIntroducerKind::CursorUp);

        $this->expectException(Exception\InvalidArgumentException::class);

        Ansi\apply('hello', $sgr, $cursor);
    }
}
