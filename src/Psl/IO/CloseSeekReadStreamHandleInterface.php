<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\IO;

interface CloseSeekReadStreamHandleInterface extends
    CloseReadStreamHandleInterface,
    CloseSeekStreamHandleInterface,
    IO\CloseSeekReadHandleInterface,
    SeekReadStreamHandleInterface
{
}
