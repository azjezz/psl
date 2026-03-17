<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\OperatingSystemCommand;
use Psl\Ansi\OperatingSystemCommandKind;

/**
 * @pure
 */
function icon_and_title(string $text): OperatingSystemCommand
{
    return new OperatingSystemCommand(OperatingSystemCommandKind::WindowIconAndTitle, $text);
}
