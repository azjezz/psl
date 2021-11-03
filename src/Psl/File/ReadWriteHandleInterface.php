<?php

declare(strict_types=1);

namespace Psl\File;

use Psl\IO;

interface ReadWriteHandleInterface extends
    IO\CloseSeekReadWriteHandleInterface,
    ReadHandleInterface,
    WriteHandleInterface
{
}
