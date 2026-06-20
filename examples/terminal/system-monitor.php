<?php

declare(strict_types=1);

namespace Psl\Example\Terminal;

use Psl\Ansi;
use Psl\Ansi\Color;
use Psl\Ansi\Style;
use Psl\Async;
use Psl\DateTime;
use Psl\Iter;
use Psl\Math;
use Psl\PseudoRandom;
use Psl\Str;
use Psl\Terminal;
use Psl\Terminal\Event;
use Psl\Terminal\Layout;
use Psl\Terminal\Widget;
use Psl\Vec;

require __DIR__ . '/../../vendor/autoload.php';

final readonly class Process
{
    public function __construct(
        public int $pid,
        public string $command,
        public float $cpu,
        public float $mem,
        public string $status,
        public string $time,
    ) {}
}

/**
 * Generate a list of mock processes.
 *
 * @return list<Process>
 */
function generate_processes(): array
{
    $commands = [
        'php-fpm',
        'nginx',
        'mysql',
        'redis-server',
        'node',
        'postgres',
        'memcached',
        'elasticsearch',
        'rabbitmq',
        'docker',
        'sshd',
        'cron',
        'systemd',
        'containerd',
        'kubelet',
        'apache2',
        'mongod',
        'consul',
        'vault',
        'terraform',
        'grafana',
        'prometheus',
        'alertmanager',
        'etcd',
        'coredns',
        'haproxy',
        'varnish',
        'traefik',
        'envoy',
        'istio-proxy',
        'fluentd',
        'logstash',
        'filebeat',
        'telegraf',
        'influxd',
        'clickhouse',
        'kafka',
        'zookeeper',
        'minio',
        'gitea',
        'jenkins',
        'gitlab-runner',
        'buildkitd',
        'containerd-shim',
        'runc',
        'podman',
        'skopeo',
        'cadvisor',
        'kube-proxy',
        'kube-scheduler',
        'kube-apiserver',
    ];

    $statuses = ['running', 'sleeping', 'sleeping', 'sleeping', 'running'];
    $processes = [];

    foreach ($commands as $i => $cmd) {
        /** @var non-negative-int $statusIdx */
        $statusIdx = PseudoRandom\int(0, Iter\count::<string>($statuses) - 1);
        $processes[] = new Process(
            pid: 1000 + (($i * 137) % 9000),
            command: $cmd,
            cpu: (float) (PseudoRandom\int(0, 800) / 10),
            mem: (float) (PseudoRandom\int(0, 400) / 10),
            status: $statuses[$statusIdx],
            time: Str\format(
                '%02d:%02d:%02d',
                PseudoRandom\int(0, 23),
                PseudoRandom\int(0, 59),
                PseudoRandom\int(0, 59),
            ),
        );
    }

    return $processes;
}

/**
 * Random walk a value within bounds.
 */
function random_walk(float $current, float $min, float $max, float $step): float
{
    $delta = (PseudoRandom\float() - 0.5) * 2.0 * $step;

    return Math\clamp::<float>($current + $delta, $min, $max);
}

final class ViewState
{
    public int $selected = 0;
    public int $proc_scroll = 0;
    /** Sort column: 'cpu', 'mem', or 'pid' */
    public string $sort_col = 'cpu';
    /** Active tab: 0 = Overview, 1 = Details */
    public int $active_tab = 0;
    /** Visible rows in process table (updated each frame) */
    public int $visible_rows = 20;
}

final class MonitorState
{
    /** @var list<float> CPU usage per core (0–100) */
    public array $cpu_values = [45.0, 72.0, 15.0, 55.0];
    public float $mem_used = 4.2;
    public float $mem_total = 16.0;
    public float $swap_used = 1.1;
    public float $swap_total = 8.0;
    /** @var list<float> CPU history for sparkline (0.0–1.0) */
    public array $cpu_history = [];
    /** @var list<Process> */
    public array $processes;
    public ViewState $view;

    public function __construct()
    {
        $this->processes = namespace\generate_processes();
        $this->view = new ViewState();
    }
}

function render_overview(Terminal\Rect $main, MonitorState $state, Terminal\Buffer $buffer): void
{
    [$topSection, $processSection] = Layout\vertical($main, [
        Layout\fixed(8),
        Layout\fill(),
    ]);

    [$cpuSection, $memSection] = Layout\horizontal($topSection, [
        Layout\fill(),
        Layout\fixed(32),
    ]);

    $cpuBlock = Widget\Block::new()
        ->title(' CPU ')
        ->titleStyle(Ansi\foreground(Color\bright_cyan()), Style\bold())
        ->border(Widget\Border::rounded());

    $cpuBlock->render($cpuSection, Widget\Paragraph::new([]), $buffer);
    $cpuInner = $cpuBlock->innerArea($cpuSection);

    $cpuRows = Layout\vertical($cpuInner, [
        Layout\fixed(1),
        Layout\fixed(1),
        Layout\fixed(1),
        Layout\fixed(1),
        Layout\fixed(1),
        Layout\fill(),
    ]);

    $cpuLabels = ['CPU 1', 'CPU 2', 'CPU 3', 'CPU 4'];
    foreach ($state->cpu_values as $i => $cpuVal) {
        $color = match (true) {
            $cpuVal > 80.0 => Color\bright_red(),
            $cpuVal > 50.0 => Color\bright_yellow(),
            default => Color\bright_green(),
        };

        Widget\Gauge::new()
            ->ratio($cpuVal / 100.0)
            ->label($cpuLabels[$i])
            ->filledStyle(Ansi\foreground($color))
            ->emptyStyle(Ansi\foreground(Color\bright_black()))
            ->labelStyle(Ansi\foreground(Color\bright_white()))
            ->render($cpuRows[$i], $buffer);
    }

    if ($state->cpu_history !== []) {
        Widget\Sparkline::new($state->cpu_history)->style(Ansi\foreground(Color\bright_cyan()))->render(
            $cpuRows[5],
            $buffer,
        );
    }

    $memBlock = Widget\Block::new()
        ->title(' Memory ')
        ->titleStyle(Ansi\foreground(Color\bright_green()), Style\bold())
        ->border(Widget\Border::rounded());

    $memBlock->render($memSection, Widget\Paragraph::new([]), $buffer);
    $memInner = $memBlock->innerArea($memSection);

    $memRows = Layout\vertical($memInner, [
        Layout\fixed(1),
        Layout\fixed(1),
        Layout\fixed(1),
        Layout\fill(),
    ]);

    $memRatio = $state->mem_used / $state->mem_total;
    $memColor = match (true) {
        $memRatio > 0.8 => Color\bright_red(),
        $memRatio > 0.5 => Color\bright_yellow(),
        default => Color\bright_green(),
    };

    Widget\Gauge::new()
        ->ratio($memRatio)
        ->label('Used')
        ->filledStyle(Ansi\foreground($memColor))
        ->emptyStyle(Ansi\foreground(Color\bright_black()))
        ->labelStyle(Ansi\foreground(Color\bright_white()))
        ->render($memRows[0], $buffer);

    $swapRatio = $state->swap_total > 0.0 ? $state->swap_used / $state->swap_total : 0.0;

    Widget\Gauge::new()
        ->ratio($swapRatio)
        ->label('Swap')
        ->filledStyle(Ansi\foreground(Color\bright_yellow()))
        ->emptyStyle(Ansi\foreground(Color\bright_black()))
        ->labelStyle(Ansi\foreground(Color\bright_white()))
        ->render($memRows[1], $buffer);

    Widget\Paragraph::new([
        Widget\Line::new([
            Widget\Span::styled(
                Str\format('%.1fG / %.1fG', $state->mem_used, $state->mem_total),
                Ansi\foreground(Color\bright_black()),
            ),
        ]),
        Widget\Line::new([
            Widget\Span::styled(
                Str\format('Swap: %.1fG / %.1fG', $state->swap_used, $state->swap_total),
                Ansi\foreground(Color\bright_black()),
            ),
        ]),
    ])->render($memRows[3], $buffer);

    $tableRows = Vec\map::<int, Process, array>($state->processes, static fn(Process $p): array => [
        Widget\Span::raw((string) $p->pid),
        Widget\Span::raw($p->command),
        Widget\Span::styled(Str\format('%.1f', $p->cpu), ...match (true) {
            $p->cpu > 50.0 => [Ansi\foreground(Color\bright_red())],
            $p->cpu > 25.0 => [Ansi\foreground(Color\bright_yellow())],
            default => [],
        }),
        Widget\Span::styled(
            Str\format('%.1f', $p->mem),
            ...$p->mem > 30.0 ? [Ansi\foreground(Color\bright_yellow())] : [],
        ),
        Widget\Span::styled(
            $p->status,
            Ansi\foreground($p->status === 'running' ? Color\bright_green() : Color\bright_black()),
        ),
        Widget\Span::raw($p->time),
    ]);

    $sortIndicator = match ($state->view->sort_col) {
        'cpu' => ['PID', 'COMMAND', 'CPU% ▼', 'MEM%', 'STATUS', 'TIME'],
        'mem' => ['PID', 'COMMAND', 'CPU%', 'MEM% ▼', 'STATUS', 'TIME'],
        default => ['PID ▼', 'COMMAND', 'CPU%', 'MEM%', 'STATUS', 'TIME'],
    };

    $table = Widget\Table::new()
        ->headers($sortIndicator)
        ->widths([8, 18, 8, 8, 12, 10])
        ->headerStyle(Ansi\foreground(Color\bright_cyan()), Style\bold())
        ->rows($tableRows)
        ->highlight($state->view->selected)
        ->highlightStyle(Ansi\foreground(Color\bright_white()), Ansi\background(Color\ansi256(236)), Style\bold())
        ->scroll($state->view->proc_scroll);

    $procBlock = Widget\Block::new()
        ->title(' Processes ')
        ->titleStyle(Ansi\foreground(Color\bright_magenta()), Style\bold())
        ->border(Widget\Border::rounded(Ansi\foreground(Color\ansi256(240))))
        ->padding(right: 2);

    $procBlock->render($processSection, $table, $buffer);

    $processCount = Iter\count::<Process>($state->processes);
    $blockInner = Widget\Block::new()->border(Widget\Border::rounded())->innerArea($processSection);
    $visibleRows = Math\maxva::<int>(1, $blockInner->height - 2);
    $state->view->visible_rows = $visibleRows;

    $scrollbarRect = new Terminal\Rect($processSection->right() - 2, $blockInner->y, 1, $blockInner->height);

    Widget\Scrollbar::new()
        ->contentLength($processCount)
        ->viewportLength($visibleRows)
        ->position($state->view->proc_scroll)
        ->thumbStyle(Ansi\foreground(Color\bright_white()))
        ->trackStyle(Ansi\foreground(Color\ansi256(238)))
        ->render($scrollbarRect, $buffer);
}

function render_details(Terminal\Rect $main, MonitorState $state, Terminal\Buffer $buffer): void
{
    [$chartSection, $infoSection] = Layout\vertical($main, [
        Layout\fill(),
        Layout\fixed(6),
    ]);

    /** @var list<array{string, float}> $cpuData */
    $cpuData = [];
    foreach ($state->cpu_values as $i => $val) {
        $cpuData[] = ['CPU' . ($i + 1), $val / 100.0];
    }

    $chartBlock = Widget\Block::new()
        ->title(' CPU Cores ')
        ->titleStyle(Ansi\foreground(Color\bright_cyan()), Style\bold())
        ->border(Widget\Border::rounded());

    $chartBlock->render($chartSection, Widget\Paragraph::new([]), $buffer);
    $chartInner = $chartBlock->innerArea($chartSection);

    Widget\BarChart::new()
        ->data($cpuData)
        ->barWidth(8)
        ->barGap(2)
        ->barStyle(Ansi\foreground(Color\bright_cyan()))
        ->labelStyle(Ansi\foreground(Color\bright_white()))
        ->render($chartInner, $buffer);

    $infoBlock = Widget\Block::new()
        ->title(' System Info ')
        ->titleStyle(Ansi\foreground(Color\bright_green()), Style\bold())
        ->border(Widget\Border::rounded());

    $avgCpu = Iter\count::<float>($state->cpu_values) > 0
        ? Math\sum_floats($state->cpu_values) / Iter\count::<float>($state->cpu_values)
        : 0.0;

    $infoBlock->render(
        $infoSection,
        Widget\Paragraph::new([
            Widget\Line::new([
                Widget\Span::styled('Average CPU: ', Ansi\foreground(Color\bright_white())),
                Widget\Span::styled(
                    Str\format('%.1f%%', $avgCpu),
                    Ansi\foreground(match (true) {
                        $avgCpu > 80.0 => Color\bright_red(),
                        $avgCpu > 50.0 => Color\bright_yellow(),
                        default => Color\bright_green(),
                    }),
                ),
                Widget\Span::styled('    Memory: ', Ansi\foreground(Color\bright_white())),
                Widget\Span::styled(
                    Str\format('%.1fG / %.1fG', $state->mem_used, $state->mem_total),
                    Ansi\foreground(Color\bright_green()),
                ),
            ]),
            Widget\Line::new([
                Widget\Span::styled('Cores: ', Ansi\foreground(Color\bright_white())),
                Widget\Span::styled((string) Iter\count::<float>($state->cpu_values), Ansi\foreground(Color\bright_cyan())),
                Widget\Span::styled('    Swap: ', Ansi\foreground(Color\bright_white())),
                Widget\Span::styled(
                    Str\format('%.1fG / %.1fG', $state->swap_used, $state->swap_total),
                    Ansi\foreground(Color\bright_yellow()),
                ),
            ]),
            Widget\Line::new([
                Widget\Span::styled('Processes: ', Ansi\foreground(Color\bright_white())),
                Widget\Span::styled((string) Iter\count::<Process>($state->processes), Ansi\foreground(Color\bright_magenta())),
                Widget\Span::styled('    History points: ', Ansi\foreground(Color\bright_white())),
                Widget\Span::styled((string) Iter\count::<float>($state->cpu_history), Ansi\foreground(Color\bright_cyan())),
            ]),
        ]),
        $buffer,
    );
}

Async\main(static function (): int {
    $app = Terminal\Application::create::<MonitorState>(new MonitorState(), title: 'System Monitor');

    $app->interval(DateTime\Duration::seconds(1), static function (MonitorState $state): void {
        foreach ($state->cpu_values as $i => $v) {
            $state->cpu_values[$i] = namespace\random_walk($v, 0.0, 100.0, 10.0);
        }

        $state->mem_used = namespace\random_walk($state->mem_used, 0.5, $state->mem_total - 0.5, 0.3);
        $state->swap_used = namespace\random_walk($state->swap_used, 0.0, $state->swap_total, 0.1);

        $avg = Math\sum_floats($state->cpu_values) / Iter\count::<float>($state->cpu_values);
        $state->cpu_history[] = $avg / 100.0;
        if (Iter\count::<float>($state->cpu_history) > 120) {
            $state->cpu_history = Vec\drop::<float>($state->cpu_history, 1);
        }

        $updated = [];
        foreach ($state->processes as $proc) {
            $updated[] = new Process(
                pid: $proc->pid,
                command: $proc->command,
                cpu: namespace\random_walk($proc->cpu, 0.0, 100.0, 5.0),
                mem: namespace\random_walk($proc->mem, 0.0, 50.0, 2.0),
                status: $proc->status,
                time: $proc->time,
            );
        }

        $state->processes = Vec\sort::<Process>($updated, static fn(Process $a, Process $b): int => match (
            $state->view->sort_col
        ) {
            'cpu' => $b->cpu <=> $a->cpu,
            'mem' => $b->mem <=> $a->mem,
            default => $a->pid <=> $b->pid,
        });
    });

    $app->on::<Event\Key>(Event\Key::class, static function (Event\Key $event, MonitorState $state) use ($app): void {
        if ($event->is('ctrl+c')) {
            $app->stop();
            return;
        }

        if ($event->is('tab')) {
            $state->view->active_tab = ($state->view->active_tab + 1) % 2;
            return;
        }

        if ($state->view->active_tab !== 0) {
            return;
        }

        $processCount = Iter\count::<Process>($state->processes);

        if ($event->is('up') && $processCount > 0) {
            $state->view->selected = Math\maxva::<int>(0, $state->view->selected - 1);
            if ($state->view->selected < $state->view->proc_scroll) {
                $state->view->proc_scroll = $state->view->selected;
            }

            return;
        }

        if ($event->is('down') && $processCount > 0) {
            $state->view->selected = Math\minva::<int>($processCount - 1, $state->view->selected + 1);
            $visible = $state->view->visible_rows;
            if ($state->view->selected >= ($state->view->proc_scroll + $visible)) {
                $state->view->proc_scroll = $state->view->selected - $visible + 1;
            }

            return;
        }

        if ($event->char === 's' || $event->char === 'S') {
            $state->view->sort_col = match ($state->view->sort_col) {
                'cpu' => 'mem',
                'mem' => 'pid',
                default => 'cpu',
            };

            return;
        }

        if ($event->is('home') && $processCount > 0) {
            $state->view->selected = 0;
            $state->view->proc_scroll = 0;
            return;
        }

        if ($event->is('end') && $processCount > 0) {
            $state->view->selected = $processCount - 1;
            $state->view->proc_scroll = Math\maxva::<int>(0, $processCount - $state->view->visible_rows);
            return;
        }
    });

    $app->on::<Event\Mouse>(Event\Mouse::class, static function (Event\Mouse $event, MonitorState $state): void {
        if ($state->view->active_tab !== 0) {
            return;
        }

        $processCount = Iter\count::<Process>($state->processes);

        if ($event->kind === Event\MouseKind::ScrollUp) {
            $state->view->proc_scroll = Math\maxva::<int>(0, $state->view->proc_scroll - 3);
        }

        if ($event->kind === Event\MouseKind::ScrollDown) {
            $state->view->proc_scroll = Math\minva::<int>(Math\maxva::<int>(0, $processCount - 1), $state->view->proc_scroll + 3);
        }
    });

    return $app->run(static function (Terminal\Frame $frame, MonitorState $state): void {
        $buffer = $frame->buffer();

        [$tabBar, $main, $statusBar] = Layout\vertical($frame, [
            Layout\fixed(1),
            Layout\fill(),
            Layout\fixed(1),
        ]);

        Widget\Tabs::new()
            ->titles(['Overview', 'Details'])
            ->highlight($state->view->active_tab)
            ->activeStyle(Ansi\foreground(Color\bright_cyan()), Style\bold())
            ->inactiveStyle(Ansi\foreground(Color\bright_black()))
            ->render($tabBar, $buffer);

        if ($state->view->active_tab === 0) {
            namespace\render_overview($main, $state, $buffer);
        }

        if ($state->view->active_tab === 1) {
            namespace\render_details($main, $state, $buffer);
        }

        $sortLabel = match ($state->view->sort_col) {
            'cpu' => 'CPU%',
            'mem' => 'MEM%',
            default => 'PID',
        };

        $rightText = "Tab: switch view | \u{2191}/\u{2193} navigate | s sort | Ctrl+C quit ";
        $rightLen = Str\width($rightText);

        [$statusLeft, $statusRight] = Layout\horizontal($statusBar, [
            Layout\fill(),
            Layout\fixed($rightLen),
        ]);

        $avgCpu = Iter\count::<float>($state->cpu_values) > 0
            ? Math\sum_floats($state->cpu_values) / Iter\count::<float>($state->cpu_values)
            : 0.0;

        Widget\Paragraph::new([Widget\Line::new([
            Widget\Span::styled(
                Str\format(
                    ' CPU: %.0f%%  MEM: %.1fG/%dG  Sort: %s',
                    $avgCpu,
                    $state->mem_used,
                    (int) $state->mem_total,
                    $sortLabel,
                ),
                Ansi\foreground(Color\bright_black()),
            ),
        ])])->render($statusLeft, $buffer);

        Widget\Paragraph::new([Widget\Line::new([
            Widget\Span::styled($rightText, Ansi\foreground(Color\bright_black())),
        ])])->alignment(Widget\Alignment::Right)->render($statusRight, $buffer);
    });
});
