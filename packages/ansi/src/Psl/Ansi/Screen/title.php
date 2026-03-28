<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\OperatingSystemCommand;
use Psl\Ansi\OperatingSystemCommandKind;

/**
 * @pure
 *
 * @api
 */
function title(string $title): OperatingSystemCommand
{
    return new OperatingSystemCommand(OperatingSystemCommandKind::WindowTitle, $title);
}
