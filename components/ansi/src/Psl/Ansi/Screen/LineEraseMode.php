<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

enum LineEraseMode: int
{
    case Right = 0;
    case Left = 1;
    case Full = 2;
}
