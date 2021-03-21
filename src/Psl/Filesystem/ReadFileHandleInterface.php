<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\IO;

interface ReadFileHandleInterface extends FileHandleInterface, IO\CloseSeekReadHandleInterface
{
}
