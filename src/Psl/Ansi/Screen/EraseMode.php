<?php

declare(strict_types=1);

namespace Psl\Ansi\Screen;

enum EraseMode: int
{
    case Below = 0;
    case Above = 1;
    case Full = 2;
    case FullWithScrollback = 3;
}
