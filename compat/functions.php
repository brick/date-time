<?php

/**
 * intdiv() compatibility function for PHP 5
 */

namespace Brick\DateTime {
    if (! function_exists('intdiv')) {
        function intdiv($a, $b) {
            return ($a - ($a % $b)) / $b;
        }
    }
}

namespace Brick\DateTime\Utility {
    if (! function_exists('intdiv')) {
        function intdiv($a, $b) {
            return ($a - ($a % $b)) / $b;
        }
    }
}
