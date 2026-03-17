<?php

declare(strict_types=1);

namespace Psl\Terminal\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\IO;
use Psl\Terminal\Internal\RawMode;

final class RawModeTest extends TestCase
{
    protected function setUp(): void
    {
        if (!IO\is_terminal()) {
            static::markTestSkipped('No TTY available');
        }
    }

    public function testEnableAndRestore(): void
    {
        $rawMode = new RawMode();

        try {
            $rawMode->enable();
        } finally {
            $rawMode->restore();
        }

        static::assertTrue(true);
    }

    public function testRestoreWithoutEnableIsNoOp(): void
    {
        $rawMode = new RawMode();
        $rawMode->restore();

        static::assertTrue(true);
    }

    public function testDoubleRestoreIsNoOp(): void
    {
        $rawMode = new RawMode();

        try {
            $rawMode->enable();
        } finally {
            $rawMode->restore();
        }

        $rawMode->restore();

        static::assertTrue(true);
    }

    public function testEnableRestoreEnableRestore(): void
    {
        $rawMode = new RawMode();

        try {
            $rawMode->enable();
        } finally {
            $rawMode->restore();
        }

        try {
            $rawMode->enable();
        } finally {
            $rawMode->restore();
        }

        static::assertTrue(true);
    }
}
