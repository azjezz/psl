<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\IO;

interface CloseSeekWriteStreamHandleInterface extends
    CloseSeekStreamHandleInterface,
    CloseWriteStreamHandleInterface,
    IO\CloseSeekWriteHandleInterface,
    SeekWriteStreamHandleInterface
{
}
