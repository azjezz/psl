<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;

interface CloseSeekWriteHandleInterface extends
    CloseSeekHandleInterface,
    CloseWriteHandleInterface,
    IO\CloseSeekWriteHandleInterface,
    SeekWriteHandleInterface
{
}
