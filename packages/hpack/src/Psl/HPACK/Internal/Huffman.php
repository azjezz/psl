<?php

declare(strict_types=1);

namespace Psl\HPACK\Internal;

use Psl\HPACK\Exception\DecodingException;

use function array_shift;
use function chr;
use function ord;
use function str_contains;
use function strlen;

/**
 * Canonical Huffman codec per RFC 7541 Appendix B.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc7541#appendix-B
 *
 * @internal
 *
 * @mago-expect lint:excessive-nesting
 */
final class Huffman
{
    /**
     * Huffman code table: [code, bit length] indexed by symbol (0-255 + EOS=256).
     *
     * @var list<array{int, int}>
     */
    private const array CODE_TABLE = [
        [0x1ff8, 13],
        [0x7f_ffd8, 23],
        [0xfff_ffe2, 28],
        [0xfff_ffe3, 28],
        [0xfff_ffe4, 28],
        [0xfff_ffe5, 28],
        [0xfff_ffe6, 28],
        [0xfff_ffe7, 28],
        [0xfff_ffe8, 28],
        [0xff_ffea, 24],
        [0x3fff_fffc, 30],
        [0xfff_ffe9, 28],
        [0xfff_ffea, 28],
        [0x3fff_fffd, 30],
        [0xfff_ffeb, 28],
        [0xfff_ffec, 28],
        [0xfff_ffed, 28],
        [0xfff_ffee, 28],
        [0xfff_ffef, 28],
        [0xfff_fff0, 28],
        [0xfff_fff1, 28],
        [0xfff_fff2, 28],
        [0x3fff_fffe, 30],
        [0xfff_fff3, 28],
        [0xfff_fff4, 28],
        [0xfff_fff5, 28],
        [0xfff_fff6, 28],
        [0xfff_fff7, 28],
        [0xfff_fff8, 28],
        [0xfff_fff9, 28],
        [0xfff_fffa, 28],
        [0xfff_fffb, 28],
        [0x14, 6],
        [0x3f8, 10],
        [0x3f9, 10],
        [0xffa, 12],
        [0x1ff9, 13],
        [0x15, 6],
        [0xf8, 8],
        [0x7fa, 11],
        [0x3fa, 10],
        [0x3fb, 10],
        [0xf9, 8],
        [0x7fb, 11],
        [0xfa, 8],
        [0x16, 6],
        [0x17, 6],
        [0x18, 6],
        [0x0, 5],
        [0x1, 5],
        [0x2, 5],
        [0x19, 6],
        [0x1a, 6],
        [0x1b, 6],
        [0x1c, 6],
        [0x1d, 6],
        [0x1e, 6],
        [0x1f, 6],
        [0x5c, 7],
        [0xfb, 8],
        [0x7ffc, 15],
        [0x20, 6],
        [0xffb, 12],
        [0x3fc, 10],
        [0x1ffa, 13],
        [0x21, 6],
        [0x5d, 7],
        [0x5e, 7],
        [0x5f, 7],
        [0x60, 7],
        [0x61, 7],
        [0x62, 7],
        [0x63, 7],
        [0x64, 7],
        [0x65, 7],
        [0x66, 7],
        [0x67, 7],
        [0x68, 7],
        [0x69, 7],
        [0x6a, 7],
        [0x6b, 7],
        [0x6c, 7],
        [0x6d, 7],
        [0x6e, 7],
        [0x6f, 7],
        [0x70, 7],
        [0x71, 7],
        [0x72, 7],
        [0xfc, 8],
        [0x73, 7],
        [0xfd, 8],
        [0x1ffb, 13],
        [0x7_fff0, 19],
        [0x1ffc, 13],
        [0x3ffc, 14],
        [0x22, 6],
        [0x7ffd, 15],
        [0x3, 5],
        [0x23, 6],
        [0x4, 5],
        [0x24, 6],
        [0x5, 5],
        [0x25, 6],
        [0x26, 6],
        [0x27, 6],
        [0x6, 5],
        [0x74, 7],
        [0x75, 7],
        [0x28, 6],
        [0x29, 6],
        [0x2a, 6],
        [0x7, 5],
        [0x2b, 6],
        [0x76, 7],
        [0x2c, 6],
        [0x8, 5],
        [0x9, 5],
        [0x2d, 6],
        [0x77, 7],
        [0x78, 7],
        [0x79, 7],
        [0x7a, 7],
        [0x7b, 7],
        [0x7ffe, 15],
        [0x7fc, 11],
        [0x3ffd, 14],
        [0x1ffd, 13],
        [0xfff_fffc, 28],
        [0xf_ffe6, 20],
        [0x3f_ffd2, 22],
        [0xf_ffe7, 20],
        [0xf_ffe8, 20],
        [0x3f_ffd3, 22],
        [0x3f_ffd4, 22],
        [0x3f_ffd5, 22],
        [0x7f_ffd9, 23],
        [0x3f_ffd6, 22],
        [0x7f_ffda, 23],
        [0x7f_ffdb, 23],
        [0x7f_ffdc, 23],
        [0x7f_ffdd, 23],
        [0x7f_ffde, 23],
        [0xff_ffeb, 24],
        [0x7f_ffdf, 23],
        [0xff_ffec, 24],
        [0xff_ffed, 24],
        [0x3f_ffd7, 22],
        [0x7f_ffe0, 23],
        [0xff_ffee, 24],
        [0x7f_ffe1, 23],
        [0x7f_ffe2, 23],
        [0x7f_ffe3, 23],
        [0x7f_ffe4, 23],
        [0x1f_ffdc, 21],
        [0x3f_ffd8, 22],
        [0x7f_ffe5, 23],
        [0x3f_ffd9, 22],
        [0x7f_ffe6, 23],
        [0x7f_ffe7, 23],
        [0xff_ffef, 24],
        [0x3f_ffda, 22],
        [0x1f_ffdd, 21],
        [0xf_ffe9, 20],
        [0x3f_ffdb, 22],
        [0x3f_ffdc, 22],
        [0x7f_ffe8, 23],
        [0x7f_ffe9, 23],
        [0x1f_ffde, 21],
        [0x7f_ffea, 23],
        [0x3f_ffdd, 22],
        [0x3f_ffde, 22],
        [0xff_fff0, 24],
        [0x1f_ffdf, 21],
        [0x3f_ffdf, 22],
        [0x7f_ffeb, 23],
        [0x7f_ffec, 23],
        [0x1f_ffe0, 21],
        [0x1f_ffe1, 21],
        [0x3f_ffe0, 22],
        [0x1f_ffe2, 21],
        [0x7f_ffed, 23],
        [0x3f_ffe1, 22],
        [0x7f_ffee, 23],
        [0x7f_ffef, 23],
        [0xf_ffea, 20],
        [0x3f_ffe2, 22],
        [0x3f_ffe3, 22],
        [0x3f_ffe4, 22],
        [0x7f_fff0, 23],
        [0x3f_ffe5, 22],
        [0x3f_ffe6, 22],
        [0x7f_fff1, 23],
        [0x3ff_ffe0, 26],
        [0x3ff_ffe1, 26],
        [0xf_ffeb, 20],
        [0x7_fff1, 19],
        [0x3f_ffe7, 22],
        [0x7f_fff2, 23],
        [0x3f_ffe8, 22],
        [0x1ff_ffec, 25],
        [0x3ff_ffe2, 26],
        [0x3ff_ffe3, 26],
        [0x3ff_ffe4, 26],
        [0x7ff_ffde, 27],
        [0x7ff_ffdf, 27],
        [0x3ff_ffe5, 26],
        [0xff_fff1, 24],
        [0x1ff_ffed, 25],
        [0x7_fff2, 19],
        [0x1f_ffe3, 21],
        [0x3ff_ffe6, 26],
        [0x7ff_ffe0, 27],
        [0x7ff_ffe1, 27],
        [0x3ff_ffe7, 26],
        [0x7ff_ffe2, 27],
        [0xff_fff2, 24],
        [0x1f_ffe4, 21],
        [0x1f_ffe5, 21],
        [0x3ff_ffe8, 26],
        [0x3ff_ffe9, 26],
        [0xfff_fffd, 28],
        [0x7ff_ffe3, 27],
        [0x7ff_ffe4, 27],
        [0x7ff_ffe5, 27],
        [0xf_ffec, 20],
        [0xff_fff3, 24],
        [0xf_ffed, 20],
        [0x1f_ffe6, 21],
        [0x3f_ffe9, 22],
        [0x1f_ffe7, 21],
        [0x1f_ffe8, 21],
        [0x7f_fff3, 23],
        [0x3f_ffea, 22],
        [0x3f_ffeb, 22],
        [0x1ff_ffee, 25],
        [0x1ff_ffef, 25],
        [0xff_fff4, 24],
        [0xff_fff5, 24],
        [0x3ff_ffea, 26],
        [0x7f_fff4, 23],
        [0x3ff_ffeb, 26],
        [0x7ff_ffe6, 27],
        [0x3ff_ffec, 26],
        [0x3ff_ffed, 26],
        [0x7ff_ffe7, 27],
        [0x7ff_ffe8, 27],
        [0x7ff_ffe9, 27],
        [0x7ff_ffea, 27],
        [0x7ff_ffeb, 27],
        [0xfff_fffe, 28],
        [0x7ff_ffec, 27],
        [0x7ff_ffed, 27],
        [0x7ff_ffee, 27],
        [0x7ff_ffef, 27],
        [0x7ff_fff0, 27],
        [0x3ff_ffee, 26],
        [0x3fff_ffff, 30], // EOS (256)
    ];

    /**
     * Decode state machine: [transitions, accept flags].
     *
     * @var null|array{list<list<array{int, int}>>, list<bool>}
     */
    private static null|array $decodeMachine = null;

    /**
     * @internal For testing only — resets the cached decode machine.
     */
    public static function resetDecodeMachine(): void
    {
        self::$decodeMachine = null;
    }

    /**
     * @var list<int>
     */
    private const array BIT_LENGTHS = [
        13,
        23,
        28,
        28,
        28,
        28,
        28,
        28,
        28,
        24,
        30,
        28,
        28,
        30,
        28,
        28,
        28,
        28,
        28,
        28,
        28,
        28,
        30,
        28,
        28,
        28,
        28,
        28,
        28,
        28,
        28,
        28,
        6,
        10,
        10,
        12,
        13,
        6,
        8,
        11,
        10,
        10,
        8,
        11,
        8,
        6,
        6,
        6,
        5,
        5,
        5,
        6,
        6,
        6,
        6,
        6,
        6,
        6,
        7,
        8,
        15,
        6,
        12,
        10,
        13,
        6,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        7,
        8,
        7,
        8,
        13,
        19,
        13,
        14,
        6,
        15,
        5,
        6,
        5,
        6,
        5,
        6,
        6,
        6,
        5,
        7,
        7,
        6,
        6,
        6,
        5,
        6,
        7,
        6,
        5,
        5,
        6,
        7,
        7,
        7,
        7,
        7,
        15,
        11,
        14,
        13,
        28,
        20,
        22,
        20,
        20,
        22,
        22,
        22,
        23,
        22,
        23,
        23,
        23,
        23,
        23,
        24,
        23,
        24,
        24,
        22,
        23,
        24,
        23,
        23,
        23,
        23,
        21,
        22,
        23,
        22,
        23,
        23,
        24,
        22,
        21,
        20,
        22,
        22,
        23,
        23,
        21,
        23,
        22,
        22,
        24,
        21,
        22,
        23,
        23,
        21,
        21,
        22,
        21,
        23,
        22,
        23,
        23,
        20,
        22,
        22,
        22,
        23,
        22,
        22,
        23,
        26,
        26,
        20,
        19,
        22,
        23,
        22,
        25,
        26,
        26,
        26,
        27,
        27,
        26,
        24,
        25,
        19,
        21,
        26,
        27,
        27,
        26,
        27,
        24,
        21,
        21,
        26,
        26,
        28,
        27,
        27,
        27,
        20,
        24,
        20,
        21,
        22,
        21,
        21,
        23,
        22,
        22,
        25,
        25,
        24,
        24,
        26,
        23,
        26,
        27,
        26,
        26,
        27,
        27,
        27,
        27,
        27,
        28,
        27,
        27,
        27,
        27,
        27,
        26,
        30,
    ];

    /**
     * Estimate the number of bits required to Huffman-encode the given data.
     *
     * @param string $data The raw data to estimate.
     *
     * @return int The estimated number of bits.
     */
    public static function estimateEncodedBits(string $data): int
    {
        $bits = 0;
        $length = strlen($data);
        for ($i = 0; $i < $length; $i++) {
            $bits += self::BIT_LENGTHS[ord($data[$i])];
        }

        return $bits;
    }

    /**
     * Huffman-encode the given data per RFC 7541 Appendix B.
     *
     * @param string $data The raw data to encode.
     *
     * @return string The Huffman-encoded bytes.
     */
    public static function encode(string $data): string
    {
        $length = strlen($data);
        if ($length === 0) {
            return '';
        }

        $buffer = 0;
        $bits = 0;
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            [$code, $codeLen] = self::CODE_TABLE[ord($data[$i])];
            $buffer = ($buffer << $codeLen) | $code;
            $bits += $codeLen;

            while ($bits >= 8) {
                $bits -= 8;
                $result .= chr(($buffer >> $bits) & 0xFF);
            }
        }

        if ($bits > 0) {
            $result .= chr((($buffer << (8 - $bits)) | ((1 << (8 - $bits)) - 1)) & 0xFF);
        }

        return $result;
    }

    /**
     * Decode a Huffman-encoded string per RFC 7541 Appendix B.
     *
     * @param string $data The Huffman-encoded bytes to decode.
     *
     * @throws DecodingException If the data contains invalid Huffman sequences or padding.
     *
     * @return string The decoded raw data.
     */
    public static function decode(string $data): string
    {
        [$table, $acceptFlags] = self::getDecodeMachine();
        $length = strlen($data);
        $state = 0;
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $byte = ord($data[$i]);

            $highNibble = ($byte >> 4) & 0x0F;
            /** @mago-expect analysis:mismatched-array-index */
            $stateTransitions = $table[$state];
            /** @mago-expect analysis:mismatched-array-index */
            [$nextState, $emit] = $stateTransitions[$highNibble];
            if ($emit === 256) {
                throw DecodingException::forEosInHuffmanData();
            }

            if ($nextState === -1) {
                throw DecodingException::forIncompleteHuffmanSequence();
            }

            if ($emit >= 0) {
                $result .= chr($emit);
            }

            $state = $nextState;

            $lowNibble = $byte & 0x0F;
            /** @mago-expect analysis:mismatched-array-index,mismatched-array-index */
            [$nextState, $emit] = $table[$state][$lowNibble];
            if ($emit === 256) {
                throw DecodingException::forEosInHuffmanData();
            }

            if ($nextState === -1) {
                throw DecodingException::forIncompleteHuffmanSequence();
            }

            if ($emit >= 0) {
                $result .= chr($emit);
            }

            $state = $nextState;
        }

        /** @mago-expect analysis:mismatched-array-index - state is always a valid index */
        if ($state !== 0 && !$acceptFlags[$state]) {
            throw DecodingException::forInvalidHuffmanPadding();
        }

        return $result;
    }

    /**
     * @return array{list<list<array{int, int}>>, list<bool>}
     */
    private static function getDecodeMachine(): array
    {
        if (self::$decodeMachine !== null) {
            return self::$decodeMachine;
        }

        $trie = self::buildTrie();
        self::$decodeMachine = self::buildNibbleTable($trie);

        return self::$decodeMachine;
    }

    /**
     * @return array<string, int>
     */
    private static function buildTrie(): array
    {
        $trie = [];
        $trie[''] = -1;

        for ($sym = 0; $sym <= 256; $sym++) {
            [$code, $codeLen] = self::CODE_TABLE[$sym];

            $path = '';
            for ($bit = $codeLen - 1; $bit >= 0; $bit--) {
                $b = ($code >> $bit) & 1;
                $path .= (string) $b;
                if (!isset($trie[$path])) {
                    $trie[$path] = -1;
                }
            }

            $trie[$path] = $sym;
        }

        return $trie;
    }

    /**
     * @param array<string, int> $trie
     *
     * @return array{list<list<array{int, int}>>, list<bool>}
     */
    private static function buildNibbleTable(array $trie): array
    {
        $stateMap = [];
        $statePaths = [];
        $states = [];
        $stateId = 0;

        $stateMap[''] = $stateId++;
        $statePaths[] = '';
        $states[] = [];

        $queue = [''];

        while ($queue !== []) {
            $bitPath = array_shift($queue);
            $currentStateId = $stateMap[$bitPath];

            $transitions = [];
            for ($nibble = 0; $nibble < 16; $nibble++) {
                $path = $bitPath;
                $emit = -1;

                for ($bit = 3; $bit >= 0; $bit--) {
                    $path .= (string) (($nibble >> $bit) & 1);

                    if (isset($trie[$path]) && $trie[$path] >= 0) {
                        $sym = $trie[$path];
                        if ($sym === 256) {
                            $transitions[] = [-1, 256];
                            continue 2;
                        }

                        $emit = $sym;
                        $path = '';
                    }
                }

                if (!isset($trie[$path])) {
                    $transitions[] = [-1, -1];
                    continue;
                }

                if (!isset($stateMap[$path])) {
                    $stateMap[$path] = $stateId++;
                    $statePaths[] = $path;
                    $states[] = [];
                    $queue[] = $path;
                }

                $transitions[] = [$stateMap[$path], $emit];
            }

            $states[$currentStateId] = $transitions;
        }

        $acceptFlags = [];
        foreach ($statePaths as $path) {
            $acceptFlags[] = $path === '' || strlen($path) <= 7 && !str_contains($path, '0');
        }

        return [$states, $acceptFlags];
    }
}
