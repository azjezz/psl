<?php

declare(strict_types=1);

namespace Psl\Process;

enum Signal: int
{
    case Hangup = 1;
    case Interrupt = 2;
    case Quit = 3;
    case Kill = 9;
    case User1 = 10;
    case User2 = 12;
    case Alarm = 14;
    case Terminate = 15;
}
