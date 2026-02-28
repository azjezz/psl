<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Ansi;

use PHPUnit\Framework\TestCase;
use Psl\Ansi\CommandInterface;
use Psl\Ansi\OperatingSystemCommand;
use Psl\Ansi\OperatingSystemCommandKind;

final class OperatingSystemCommandTest extends TestCase
{
    public function testToStringTitle(): void
    {
        $osc = new OperatingSystemCommand(OperatingSystemCommandKind::WindowTitle, 'My Title');

        static::assertSame("\e]2;My Title\e\\", $osc->toString());
    }

    public function testToStringHyperlink(): void
    {
        $osc = new OperatingSystemCommand(OperatingSystemCommandKind::Hyperlink, ';https://example.com');

        static::assertSame("\e]8;;https://example.com\e\\", $osc->toString());
    }

    public function testToStringClipboard(): void
    {
        $osc = new OperatingSystemCommand(OperatingSystemCommandKind::Clipboard, 'c;aGVsbG8=');

        static::assertSame("\e]52;c;aGVsbG8=\e\\", $osc->toString());
    }

    public function testStringable(): void
    {
        $osc = new OperatingSystemCommand(OperatingSystemCommandKind::WindowTitle, 'Test');

        static::assertSame("\e]2;Test\e\\", (string) $osc);
    }

    public function testImplementsCommandInterface(): void
    {
        $osc = new OperatingSystemCommand(OperatingSystemCommandKind::WindowTitle, 'Test');

        static::assertInstanceOf(CommandInterface::class, $osc);
    }
}
