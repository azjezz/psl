<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\IO;

interface CloseReadStreamHandleInterface extends CloseStreamHandleInterface, IO\CloseReadHandleInterface, ReadStreamHandleInterface
{
}
