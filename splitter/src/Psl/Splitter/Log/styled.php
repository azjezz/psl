<?php

declare(strict_types=1);

namespace Psl\Splitter\Log;

use Psl\Ansi;
use Psl\Str;
use Psl\Vec;

/**
 * Apply ANSI styles to a text string.
 */
function styled(string $text, Ansi\ControlSequenceIntroducer ...$style): string
{
    if ($style === []) {
        return $text;
    }

    $prefix = Str\join(Vec\map($style, static fn(Ansi\ControlSequenceIntroducer $s): string => $s->toString()), '');

    return $prefix . $text . "\e[0m";
}
