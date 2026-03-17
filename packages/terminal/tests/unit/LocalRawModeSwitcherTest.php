<?php

declare(strict_types=1);

namespace Psl\Terminal\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\IO;
use Psl\Terminal\Exception\RuntimeException;
use Psl\Terminal\LocalRawModeSwitcher;

final class LocalRawModeSwitcherTest extends TestCase
{
    public function testRestoreWithoutEnableIsNoop(): void
    {
        $switcher = new LocalRawModeSwitcher();

        $switcher->restore();

        $this->addToAssertionCount(1);
    }

    public function testEnableThrowsOnNonTty(): void
    {
        if (IO\is_terminal(IO\input_handle())) {
            static::markTestSkipped('Running in a terminal.');
        }

        $switcher = new LocalRawModeSwitcher();

        $this->expectException(RuntimeException::class);

        $switcher->enable();
    }

    public function testRestoreIsNoopOnNonTty(): void
    {
        if (IO\is_terminal(IO\input_handle())) {
            static::markTestSkipped('Running in a terminal.');
        }

        $switcher = new LocalRawModeSwitcher();

        $switcher->restore();

        $this->addToAssertionCount(1);
    }

    public function testEnableAndRestore(): void
    {
        if (!IO\is_terminal(IO\input_handle())) {
            static::markTestSkipped('Not running in a terminal.');
        }

        $switcher = new LocalRawModeSwitcher();

        $switcher->enable();
        $switcher->restore();

        $this->addToAssertionCount(1);
    }

    public function testDoubleRestoreIsNoop(): void
    {
        if (!IO\is_terminal(IO\input_handle())) {
            static::markTestSkipped('Not running in a terminal.');
        }

        $switcher = new LocalRawModeSwitcher();

        $switcher->enable();
        $switcher->restore();
        $switcher->restore();

        $this->addToAssertionCount(1);
    }
}
