<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Event;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Event\Mouse;
use Psl\Terminal\Event\MouseButton;
use Psl\Terminal\Event\MouseKind;
use Psl\Terminal\Event\MouseModifiers;

final class MouseTest extends TestCase
{
    public function testConstruction(): void
    {
        $modifiers = new MouseModifiers(MouseModifiers::SHIFT);
        $mouse = new Mouse(MouseKind::Press, 10, 5, MouseButton::Left, $modifiers);

        static::assertSame(MouseKind::Press, $mouse->kind);
        static::assertSame(10, $mouse->column);
        static::assertSame(5, $mouse->row);
        static::assertSame(MouseButton::Left, $mouse->button);
        static::assertTrue($mouse->modifiers->shift());
        static::assertFalse($mouse->modifiers->alt());
        static::assertFalse($mouse->modifiers->ctrl());
    }

    public function testDefaultModifiers(): void
    {
        $mouse = new Mouse(MouseKind::ScrollUp, 0, 0);

        static::assertSame(MouseButton::None, $mouse->button);
        static::assertSame(0, $mouse->modifiers->value);
        static::assertFalse($mouse->modifiers->shift());
        static::assertFalse($mouse->modifiers->alt());
        static::assertFalse($mouse->modifiers->ctrl());
    }

    public function testModifierCombinations(): void
    {
        $modifiers = new MouseModifiers(MouseModifiers::SHIFT | MouseModifiers::CTRL);
        $mouse = new Mouse(MouseKind::Press, 1, 1, MouseButton::Right, $modifiers);

        static::assertSame(MouseButton::Right, $mouse->button);
        static::assertTrue($mouse->modifiers->shift());
        static::assertFalse($mouse->modifiers->alt());
        static::assertTrue($mouse->modifiers->ctrl());
    }

    public function testAllButtons(): void
    {
        static::assertSame(MouseButton::Left, new Mouse(MouseKind::Press, 0, 0, MouseButton::Left)->button);
        static::assertSame(MouseButton::Middle, new Mouse(MouseKind::Press, 0, 0, MouseButton::Middle)->button);
        static::assertSame(MouseButton::Right, new Mouse(MouseKind::Press, 0, 0, MouseButton::Right)->button);
        static::assertSame(MouseButton::None, new Mouse(MouseKind::Move, 0, 0, MouseButton::None)->button);
    }
}
