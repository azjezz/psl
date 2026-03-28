<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

/**
 * @pure
 *
 * @api
 */
function bracketed_paste_end(): string
{
    return "\e[201~";
}
