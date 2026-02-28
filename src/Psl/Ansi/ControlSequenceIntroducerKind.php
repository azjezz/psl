<?php

declare(strict_types=1);

namespace Psl\Ansi;

enum ControlSequenceIntroducerKind: string
{
    case SelectGraphicRendition = 'm';
    case CursorUp = 'A';
    case CursorDown = 'B';
    case CursorForward = 'C';
    case CursorBack = 'D';
    case CursorMoveTo = 'H';
    case EraseInDisplay = 'J';
    case EraseInLine = 'K';
    case ScrollUp = 'S';
    case ScrollDown = 'T';
    case SaveCursor = 's';
    case RestoreCursor = 'u';
    case SetMode = 'h';
    case ResetMode = 'l';
    case DeviceStatusReport = 'n';
}
