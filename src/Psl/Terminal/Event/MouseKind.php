<?php

declare(strict_types=1);

namespace Psl\Terminal\Event;

enum MouseKind
{
    case Press;
    case Release;
    case Drag;
    case ScrollUp;
    case ScrollDown;
    case Move;
}
