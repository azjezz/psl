<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal\Event;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Event\Resize;

final class ResizeTest extends TestCase
{
    public function testProperties(): void
    {
        $event = new Resize(120, 40);

        static::assertSame(120, $event->width);
        static::assertSame(40, $event->height);
    }
}
