<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\OperatingSystemCommand;
use Psl\Ansi\OperatingSystemCommandKind;
use Psl\Encoding\Base64;

/**
 * @pure
 */
function clipboard(string $data): OperatingSystemCommand
{
    return new OperatingSystemCommand(OperatingSystemCommandKind::Clipboard, 'c;' . Base64\encode($data));
}
