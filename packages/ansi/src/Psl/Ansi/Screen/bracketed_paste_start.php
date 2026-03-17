<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

/**
 * @pure
 */
function bracketed_paste_start(): string
{
    return "\e[200~";
}
