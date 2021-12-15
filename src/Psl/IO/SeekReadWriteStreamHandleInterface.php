<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\IO;

interface SeekReadWriteStreamHandleInterface extends
    IO\SeekReadWriteHandleInterface,
    ReadWriteStreamHandleInterface,
    SeekReadStreamHandleInterface,
    SeekWriteStreamHandleInterface
{
}
