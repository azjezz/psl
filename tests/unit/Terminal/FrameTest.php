<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Buffer;
use Psl\Terminal\Frame;
use Psl\Terminal\Rect;

final class FrameTest extends TestCase
{
    public function testConstruction(): void
    {
        $rect = Rect::fromSize(80, 24);
        $buffer = new Buffer(80, 24);
        $frame = new Frame($rect, $buffer);

        static::assertSame($rect, $frame->rect());
        static::assertSame($buffer, $frame->buffer());
    }

    public function testSetRect(): void
    {
        $rect = Rect::fromSize(80, 24);
        $buffer = new Buffer(80, 24);
        $frame = new Frame($rect, $buffer);

        $newRect = Rect::fromSize(120, 40);
        $frame->setRect($newRect);

        static::assertSame($newRect, $frame->rect());
    }
}
