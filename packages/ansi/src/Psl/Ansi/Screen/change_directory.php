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
function change_directory(string $path): OperatingSystemCommand
{
    return new OperatingSystemCommand(OperatingSystemCommandKind::ChangeDirectory, $path);
}
