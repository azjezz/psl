<?php

declare(strict_types=1);

namespace Psl\Comparison;

enum Order : int
{
    case Less = -1;
    case Equal = 0;
    case Greater = 1;
}
