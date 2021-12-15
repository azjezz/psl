<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\IO;

interface SeekReadStreamHandleInterface extends IO\SeekReadHandleInterface, ReadStreamHandleInterface, SeekStreamHandleInterface
{
}
