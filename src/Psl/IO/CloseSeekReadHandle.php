<?php

declare(strict_types=1);

namespace Psl\IO;

interface CloseSeekReadHandle extends CloseReadHandle, CloseSeekHandle, SeekReadHandle
{
}
