<?php

declare(strict_types=1);

namespace Psl\Ansi\Color;

enum ColorKind
{
    case Basic;
    case Ansi256;
    case Rgb;
}
