<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\IO;

interface CloseSeekReadWriteStreamHandleInterface extends
    CloseReadWriteStreamHandleInterface,
    CloseSeekReadStreamHandleInterface,
    CloseSeekWriteStreamHandleInterface,
    IO\CloseSeekReadWriteHandleInterface,
    SeekReadWriteStreamHandleInterface
{
}
