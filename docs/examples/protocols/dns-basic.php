<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\DNS;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\RecordType;

// System resolver mirrors your OS DNS settings
$resolver = new DNS\SystemResolver();

// Query A records
$response = $resolver->query('example.com', RecordType::A);

// Check the result
if ($response->code->isSuccess()) {
    foreach ($response->getAnswerRecords(ARecord::class) as $record) {
        $record->address; // Psl\IP\Address
    }
}
