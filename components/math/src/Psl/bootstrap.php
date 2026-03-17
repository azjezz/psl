<?php

declare(strict_types=1);

(static function (): void {
    $constants = [
        'Psl\Math\INFINITY' => __DIR__ . '/Math/constants.php',
    ];

    $functions = [
        'Psl\Math\abs' => __DIR__ . '/Math/abs.php',
        'Psl\Math\acos' => __DIR__ . '/Math/acos.php',
        'Psl\Math\asin' => __DIR__ . '/Math/asin.php',
        'Psl\Math\atan' => __DIR__ . '/Math/atan.php',
        'Psl\Math\atan2' => __DIR__ . '/Math/atan2.php',
        'Psl\Math\base_convert' => __DIR__ . '/Math/base_convert.php',
        'Psl\Math\ceil' => __DIR__ . '/Math/ceil.php',
        'Psl\Math\clamp' => __DIR__ . '/Math/clamp.php',
        'Psl\Math\cos' => __DIR__ . '/Math/cos.php',
        'Psl\Math\div' => __DIR__ . '/Math/div.php',
        'Psl\Math\exp' => __DIR__ . '/Math/exp.php',
        'Psl\Math\floor' => __DIR__ . '/Math/floor.php',
        'Psl\Math\from_base' => __DIR__ . '/Math/from_base.php',
        'Psl\Math\log' => __DIR__ . '/Math/log.php',
        'Psl\Math\max' => __DIR__ . '/Math/max.php',
        'Psl\Math\max_by' => __DIR__ . '/Math/max_by.php',
        'Psl\Math\maxva' => __DIR__ . '/Math/maxva.php',
        'Psl\Math\mean' => __DIR__ . '/Math/mean.php',
        'Psl\Math\median' => __DIR__ . '/Math/median.php',
        'Psl\Math\min' => __DIR__ . '/Math/min.php',
        'Psl\Math\min_by' => __DIR__ . '/Math/min_by.php',
        'Psl\Math\minva' => __DIR__ . '/Math/minva.php',
        'Psl\Math\round' => __DIR__ . '/Math/round.php',
        'Psl\Math\sin' => __DIR__ . '/Math/sin.php',
        'Psl\Math\sqrt' => __DIR__ . '/Math/sqrt.php',
        'Psl\Math\sum' => __DIR__ . '/Math/sum.php',
        'Psl\Math\sum_floats' => __DIR__ . '/Math/sum_floats.php',
        'Psl\Math\tan' => __DIR__ . '/Math/tan.php',
        'Psl\Math\to_base' => __DIR__ . '/Math/to_base.php',
    ];

    foreach ($constants as $constant => $path) {
        if (defined($constant)) {
            continue;
        }

        require_once $path;
    }

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
