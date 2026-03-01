<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Event;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Event\Mouse;
use Psl\Terminal\Event\MouseKind;
use Psl\Terminal\Event\MouseModifiers;

final class MouseTest extends TestCase
{
    public function testConstruction(): void
    {
        $modifiers = new MouseModifiers(MouseModifiers::SHIFT);
        $mouse = new Mouse(MouseKind::Press, 10, 5, $modifiers);

        static::assertSame(MouseKind::Press, $mouse->kind);
        static::assertSame(10, $mouse->column);
        static::assertSame(5, $mouse->row);
        static::assertTrue($mouse->modifiers->shift());
        static::assertFalse($mouse->modifiers->alt());
        static::assertFalse($mouse->modifiers->ctrl());
    }

    public function testDefaultModifiers(): void
    {
        $mouse = new Mouse(MouseKind::ScrollUp, 0, 0);

        static::assertSame(0, $mouse->modifiers->value);
        static::assertFalse($mouse->modifiers->shift());
        static::assertFalse($mouse->modifiers->alt());
        static::assertFalse($mouse->modifiers->ctrl());
    }

    public function testModifierCombinations(): void
    {
        $modifiers = new MouseModifiers(MouseModifiers::SHIFT | MouseModifiers::CTRL);
        $mouse = new Mouse(MouseKind::Press, 1, 1, $modifiers);

        static::assertTrue($mouse->modifiers->shift());
        static::assertFalse($mouse->modifiers->alt());
        static::assertTrue($mouse->modifiers->ctrl());
    }
}
