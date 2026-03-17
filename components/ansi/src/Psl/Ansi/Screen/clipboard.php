<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

use Psl\Ansi\OperatingSystemCommand;
use Psl\Ansi\OperatingSystemCommandKind;

use function base64_encode;

/**
 * @pure
 */
function clipboard(string $data): OperatingSystemCommand
{
    return new OperatingSystemCommand(OperatingSystemCommandKind::Clipboard, 'c;' . base64_encode($data));
}
