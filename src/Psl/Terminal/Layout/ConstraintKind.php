<?php

declare(strict_types=1);

namespace Psl\Terminal\Layout;

/**
 * The kind of a layout constraint.
 */
enum ConstraintKind
{
    case Fill;
    case Fixed;
    case Min;
    case Max;
}
