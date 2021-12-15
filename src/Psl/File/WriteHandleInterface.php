<?php

declare(strict_types=1);

namespace Psl\File;

use Psl\IO;

interface WriteHandleInterface extends HandleInterface, IO\CloseSeekWriteStreamHandleInterface
{
}
