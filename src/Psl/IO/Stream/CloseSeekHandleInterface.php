<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;

interface CloseSeekHandleInterface extends
    CloseHandleInterface,
    IO\CloseSeekHandleInterface,
    SeekHandleInterface
{
}
