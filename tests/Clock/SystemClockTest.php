<?php

declare(strict_types=1);

namespace Brick\DateTime\Clock
{
    /**
     * Re-declare microtime() in the namespace of the SystemClock,
     * to trick it into thinking it's the native PHP function.
     */
    function microtime(): string
    {
        return '0.55527600 14079491701';
    }
}

namespace Brick\DateTime\Tests\Clock
{
    use Brick\DateTime\Clock\SystemClock;
    use Brick\DateTime\Tests\AbstractTestCase;

    /**
     * Unit tests for class SystemClock.
     */
    class SystemClockTest extends AbstractTestCase
    {
        public function testSystemClock(): void
        {
            $clock = new SystemClock();

            self::assertInstantIs(14079491701, 555276000, $clock->getTime());
        }
    }
}
