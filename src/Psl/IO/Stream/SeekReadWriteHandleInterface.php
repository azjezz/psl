<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;

interface SeekReadWriteHandleInterface extends
    IO\SeekReadWriteHandleInterface,
    ReadWriteHandleInterface,
    SeekReadHandleInterface,
    SeekWriteHandleInterface
{
}
