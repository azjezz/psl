<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

use Psl\Terminal\Buffer;
use Psl\Terminal\Rect;

/**
 * A widget that can render itself into a rectangular region of a buffer.
 */
interface WidgetInterface
{
    /**
     * Render this widget into the given area of the buffer.
     */
    public function render(Rect $area, Buffer $buffer): void;
}
