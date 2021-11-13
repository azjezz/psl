<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl\IO;

interface SeekReadHandleInterface extends IO\SeekReadHandleInterface, ReadHandleInterface, SeekHandleInterface
{
}
