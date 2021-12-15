<?php

declare(strict_types=1);

namespace Psl\File;

use Psl\IO;

interface ReadHandleInterface extends HandleInterface, IO\CloseSeekReadStreamHandleInterface
{
}
