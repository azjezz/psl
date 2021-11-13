<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;

interface CloseSeekReadHandleInterface extends
    CloseReadHandleInterface,
    CloseSeekHandleInterface,
    IO\CloseSeekReadHandleInterface,
    SeekReadHandleInterface
{
}
