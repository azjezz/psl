<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\OperatingSystemCommand;
use Psl\Ansi\OperatingSystemCommandKind;

/**
 * @pure
 */
function icon(string $name): OperatingSystemCommand
{
    return new OperatingSystemCommand(OperatingSystemCommandKind::WindowIcon, $name);
}
