<?php

declare(strict_types=1);

namespace Psl\OS;

enum Family: string
{
case Windows = 'Windows';
case BSD = 'BSD';
case Darwin = 'Darwin';
case Solaris = 'Solaris';
case Linux = 'Linux';
case Unknown = 'Unknown';
    }
