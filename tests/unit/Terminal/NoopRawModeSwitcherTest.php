<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Terminal;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\NoopRawModeSwitcher;

final class NoopRawModeSwitcherTest extends TestCase
{
    public function testEnableIsNoop(): void
    {
        $switcher = new NoopRawModeSwitcher();

        $switcher->enable();

        $this->addToAssertionCount(1);
    }

    public function testRestoreIsNoop(): void
    {
        $switcher = new NoopRawModeSwitcher();

        $switcher->restore();

        $this->addToAssertionCount(1);
    }
}
