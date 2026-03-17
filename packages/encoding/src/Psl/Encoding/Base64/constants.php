<?php

declare(strict_types=1);

namespace Psl\Encoding\Base64;

// @codeCoverageIgnoreStart

/**
 * Number of raw bytes per base64-encoded line (57 bytes encode to 76 characters).
 */
const CHUNK_SIZE = 57;

/**
 * Line ending used in base64-encoded output.
 */
const LINE_ENDING = "\r\n";

// @codeCoverageIgnoreEnd
