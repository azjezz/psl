<?php

declare(strict_types=1);

namespace Psl\Example\Ansi;

use Generator;
use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Cursor;
use Psl\Ansi\Screen;
use Psl\Ansi\Style;
use Psl\Async;
use Psl\DateTime;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\Iter;
use Psl\Math;
use Psl\Str;

require __DIR__ . '/../../vendor/autoload.php';

$watcher = Async\Scheduler::onSignal(SIGINT, static function (): never {
    IO\write(
        Cursor\show()->toString()
            . Ansi\reset()->toString()
            . Screen\erase(Screen\EraseMode::Full)->toString()
            . Cursor\move_to(1, 1)->toString()
            . Screen\title('')->toString(),
    );

    IO\write_line('bye!');
    exit(0);
});

Async\Scheduler::unreference($watcher);

// hue (0–360) → RGB color via simple HSV with S=V=1
$rainbow = static function (int $hue): Color\Color {
    $h = (($hue % 360) + 360) % 360;
    $x = 1.0 - Math\abs((($h % 120) / 60.0) - 1.0);
    [$r, $g, $b] = match (true) {
        $h < 60 => [255, (int) (255 * $x), 0],
        $h < 120 => [(int) (255 * $x), 255, 0],
        $h < 180 => [0, 255, (int) (255 * $x)],
        $h < 240 => [0, (int) (255 * $x), 255],
        $h < 300 => [(int) (255 * $x), 0, 255],
        default => [255, 0, (int) (255 * $x)],
    };

    return Color\rgb($r, $g, $b);
};

$banner = [
    '██████╗  ███████╗██╗     ',
    '██╔══██╗ ██╔════╝██║     ',
    '██████╔╝ ███████╗██║     ',
    '██╔═══╝  ╚════██║██║     ',
    '██║      ███████║███████╗',
    '╚═╝      ╚══════╝╚══════╝',
];

$dots = ['⣾', '⣽', '⣻', '⢿', '⡿', '⣟', '⣯', '⣷'];

$barChars = ['▁', '▂', '▃', '▄', '▅', '▆', '▇', '█'];

$eraseEol = Screen\erase_line(Screen\LineEraseMode::Right)->toString();

$renderBanner =
    /**
     * @param list<string> $banner
     */
    static function (array $banner, int $offset) use ($rainbow, $eraseEol): string {
        $out = "\n";
        foreach ($banner as $row => $line) {
            $out .= '    ';
            foreach (Str\chunk($line) as $col => $char) {
                if (Str\trim($char) === '') {
                    $out .= $char;
                    continue;
                }

                $hue = (($col * 10) + ($row * 25) + $offset) % 360;
                $out .= Ansi\apply($char, Style\bold(), Ansi\foreground($rainbow($hue)));
            }

            $out .= $eraseEol . "\n";
        }

        return $out;
    };

$frames = Iter\rewindable(
    (
        /**
         * @return Generator<int, string>
         */
        static function () use ($banner, $dots, $barChars, $rainbow, $renderBanner): Generator {
            for ($f = 0; $f < 72; $f++) {
                $out = Cursor\move_to(1, 1)->toString() . Screen\title('PSL · ANSI Showcase')->toString();

                // Rainbow Banner
                $out .= $renderBanner($banner, $f * 5);

                // Pulsing Subtitle
                $v = (int) (155 + (100 * Math\sin(($f * Math\PI) / 36)));
                $out .=
                    "\n    "
                    . Ansi\apply('PHP Standard Library · Ansi', Style\italic(), Ansi\foreground(Color\rgb($v, $v, $v)))
                    . "\n";

                // Flowing Gradient
                $out .= "\n    ";
                for ($i = 0; $i < 50; $i++) {
                    $out .= Ansi\apply('━', Style\bold(), Ansi\foreground($rainbow((($i * 7) + ($f * 5)) % 360)));
                }

                $out .= "\n";

                // Equalizer
                $out .= "\n    ";
                for ($b = 0; $b < 32; $b++) {
                    $level = (Math\sin(($b * 0.6) + ($f * 0.18)) * 0.5) + 0.5;
                    $idx = (int) ($level * 7);
                    $hue = (($b * 11) + ($f * 5)) % 360;
                    // @mago-expect analysis:mismatched-array-index
                    $out .= Ansi\apply($barChars[$idx], Style\bold(), Ansi\foreground($rainbow($hue)));
                }

                $out .= "\n";

                // Styles
                $styles = [
                    ['Bold', Style\bold()],
                    ['Dim', Style\dim()],
                    ['Italic', Style\italic()],
                    ['Underline', Style\underline()],
                    ['Strike', Style\strikethrough()],
                    ['Reversed', Style\reversed()],
                ];

                $out .= "\n    ";
                foreach ($styles as $si => [$label, $style]) {
                    $hue = (($si * 55) + ($f * 5)) % 360;
                    $out .= Ansi\apply(" {$label} ", $style, Ansi\foreground($rainbow($hue))) . ' ';
                }

                $out .= "\n";

                // Color Palette
                $palette = [
                    Color\black(),
                    Color\red(),
                    Color\green(),
                    Color\yellow(),
                    Color\blue(),
                    Color\magenta(),
                    Color\cyan(),
                    Color\white(),
                    Color\bright_black(),
                    Color\bright_red(),
                    Color\bright_green(),
                    Color\bright_yellow(),
                    Color\bright_blue(),
                    Color\bright_magenta(),
                    Color\bright_cyan(),
                    Color\bright_white(),
                ];

                $out .= "\n    ";
                foreach ($palette as $color) {
                    $out .= Ansi\apply('  ', Ansi\background($color));
                }

                $out .= "\n";

                // Hyperlink
                $lhue = ($f * 5) % 360;
                $out .=
                    "\n    "
                    . Ansi\link(
                        '⟶  github.com/php-standard-library/php-standard-library',
                        'https://github.com/php-standard-library/php-standard-library',
                        Style\bold(),
                        Style\underline(),
                        Ansi\foreground($rainbow($lhue)),
                    )
                    . "\n";

                // Spinner
                // @mago-expect analysis:mismatched-array-index
                $out .= "\n    " . Ansi\apply($dots[$f % 8], Style\bold(), Ansi\foreground($rainbow(($f * 15) % 360)));
                $out .= ' ' . Ansi\apply('rendering...', Style\dim(), Ansi\foreground(Color\white())) . "\n";

                // Footer
                $out .= "\n    " . Ansi\apply('ctrl+c to exit', Style\dim(), Ansi\foreground(Color\bright_black()));

                yield $out;
            }
        }
    )(),
);

IO\write(
    Cursor\hide()->toString() . Screen\notify('PSL ANSI Showcase')->toString()
        . Screen\erase(Screen\EraseMode::FullWithScrollback)->toString(),
);

$frameCount = 0;
$fps = 0;
$secondStart = DateTime\Timestamp::monotonic();
while (true) {
    foreach ($frames as $frame) {
        IO\write($frame);

        $frameCount++;
        $now = DateTime\Timestamp::monotonic();
        $elapsed = $now->since($secondStart);
        if ($elapsed->getTotalSeconds() >= 1.0) {
            $fps = (int) Math\round($frameCount / $elapsed->getTotalSeconds());
            $frameCount = 0;
            $secondStart = $now;
        }

        IO\write(
            Cursor\move_to(23, 46)->toString()
                . Ansi\apply(
                    Str\pad_left((string) $fps, 3) . ' fps',
                    Style\dim(),
                    Ansi\foreground(Color\bright_black()),
                ),
        );

        Async\sleep(Duration::milliseconds(5));
    }
}
