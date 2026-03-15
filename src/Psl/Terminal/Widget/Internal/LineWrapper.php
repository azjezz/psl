<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget\Internal;

use Psl\Math;
use Psl\Str;
use Psl\Terminal\Widget\Line;
use Psl\Terminal\Widget\Span;
use Psl\Terminal\Widget\Wrap;

/**
 * Handles line wrapping for the Paragraph widget.
 *
 * @internal
 */
final class LineWrapper
{
    /**
     * @param list<Line> $lines
     *
     * @return list<Line>
     */
    public static function wrap(array $lines, Wrap $mode, int $maxWidth): array
    {
        if ($mode === Wrap::None || $maxWidth <= 0) {
            return $lines;
        }

        $result = [];
        foreach ($lines as $line) {
            $fullText = self::flattenSpans($line->spans);

            if (Str\width($fullText) <= $maxWidth) {
                $result[] = $line;
                continue;
            }

            $wrapped = match ($mode) {
                Wrap::Word => self::wrapByWord($line->spans, $fullText, $maxWidth),
                Wrap::Char => self::wrapByChar($line->spans, $fullText, $maxWidth),
            };

            foreach ($wrapped as $wrappedLine) {
                $result[] = $wrappedLine;
            }
        }

        return $result;
    }

    /**
     * @param list<Span> $spans
     */
    private static function flattenSpans(array $spans): string
    {
        $text = '';
        foreach ($spans as $span) {
            $text .= $span->content;
        }

        return $text;
    }

    /**
     * @param list<Span> $spans
     *
     * @return list<Line>
     */
    private static function wrapByWord(array $spans, string $fullText, int $maxWidth): array
    {
        $words = Str\split($fullText, ' ');
        $lines = [];
        $currentLine = '';
        $lineStartPos = 0;

        foreach ($words as $word) {
            if ($currentLine === '') {
                $currentLine = $word;
                continue;
            }

            if ((Str\width($currentLine) + 1 + Str\width($word)) <= $maxWidth) {
                $currentLine .= ' ' . $word;
                continue;
            }

            /** @var non-negative-int $lineStartPos */
            $lines[] = self::rebuildLine($lineStartPos, Str\length($currentLine), $spans);
            $lineStartPos += Str\length($currentLine) + 1; // +1 for the space
            $currentLine = $word;
        }

        if ($currentLine !== '') {
            /** @var non-negative-int $lineStartPos */
            $lines[] = self::rebuildLine($lineStartPos, Str\length($currentLine), $spans);
        }

        return $lines === [] ? [Line::empty()] : $lines;
    }

    /**
     * @param list<Span> $spans
     *
     * @return list<Line>
     */
    private static function wrapByChar(array $spans, string $fullText, int $maxWidth): array
    {
        $lines = [];
        $length = Str\length($fullText);
        $pos = 0;

        while ($pos < $length) {
            /** @var non-negative-int $safeMaxWidth */
            $safeMaxWidth = $maxWidth;
            /** @var non-negative-int $pos */
            $chunk = Str\width_slice($fullText, $pos, $safeMaxWidth);
            $chunkLen = Str\length($chunk);
            if ($chunkLen === 0) {
                break;
            }

            $lines[] = self::rebuildLine($pos, $chunkLen, $spans);
            $pos += $chunkLen;
        }

        return $lines === [] ? [Line::empty()] : $lines;
    }

    /**
     * Rebuild a line's spans from a codepoint range, preserving all styles from original spans.
     *
     * @param int<0, max> $startPos Codepoint offset in the flattened text.
     * @param int<0, max> $length Number of codepoints to include.
     * @param list<Span> $originalSpans
     */
    private static function rebuildLine(int $startPos, int $length, array $originalSpans): Line
    {
        $endPos = $startPos + $length;
        $newSpans = [];
        $currentPos = 0;

        foreach ($originalSpans as $span) {
            $spanLen = Str\length($span->content);
            $spanEnd = $currentPos + $spanLen;

            if ($spanEnd <= $startPos || $currentPos >= $endPos) {
                $currentPos = $spanEnd;
                continue;
            }

            $sliceStart = Math\maxva(0, $startPos - $currentPos);
            $sliceEnd = Math\minva($spanLen, $endPos - $currentPos);
            $sliceLen = $sliceEnd - $sliceStart;

            if ($sliceLen > 0) {
                /** @var non-negative-int $sliceStart */
                /** @var non-negative-int $sliceLen */
                $content = Str\slice($span->content, $sliceStart, $sliceLen);
                $newSpans[] = $span->withContent($content);
            }

            $currentPos = $spanEnd;
        }

        return Line::new($newSpans);
    }
}
