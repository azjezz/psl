<?php

declare(strict_types=1);

namespace Psl\Ansi;

enum OperatingSystemCommandKind: int
{
    case WindowIconAndTitle = 0;
    case WindowIcon = 1;
    case WindowTitle = 2;
    case ChangeDirectory = 7;
    case Hyperlink = 8;
    case Notify = 9;
    case Clipboard = 52;
}
