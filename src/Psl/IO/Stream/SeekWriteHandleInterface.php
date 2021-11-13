<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;

interface SeekWriteHandleInterface extends IO\SeekWriteHandleInterface, SeekHandleInterface, WriteHandleInterface
{
}
