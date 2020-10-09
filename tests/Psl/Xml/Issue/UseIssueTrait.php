<?php

declare(strict_types=1);

namespace Psl\Tests\Xml\Issue;

use Psl\Xml\Issue\Issue;
use Psl\Xml\Issue\Level;

trait UseIssueTrait
{
    private function createIssue(Level $level): Issue
    {
        return new Issue(
            $level,
            $code    = 1,
            $column  = 10,
            $message = 'message',
            $file     = 'file.xml',
            $line    = 99
        );
    }
}
