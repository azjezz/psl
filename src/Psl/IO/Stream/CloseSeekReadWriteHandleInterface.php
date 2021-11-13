<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;

interface CloseSeekReadWriteHandleInterface extends
    CloseReadWriteHandleInterface,
    CloseSeekReadHandleInterface,
    CloseSeekWriteHandleInterface,
    IO\CloseSeekReadWriteHandleInterface,
    SeekReadWriteHandleInterface
{
}
