<?php

namespace Brick\DateTime\Clock
{
    /**
     * Re-declare microtime() in the namespace of the SystemClock,
     * to trick it into thinking it's the native PHP function.
     *
     * @return string
     */
    function microtime() {
        return '0.55527600 14079491701';
    }
}

namespace Brick\DateTime\Tests\Clock
{
    use Brick\DateTime\Tests\AbstractTestCase;
    use Brick\DateTime\Clock\SystemClock;

    /**
     * Unit tests for class SystemClock.
     */
    class SystemClockTest extends AbstractTestCase
    {
        public function testSystemClock()
        {
            $clock = new SystemClock();

            $this->assertReadableInstantIs(14079491701, 555276000, $clock->getTime());
        }
    }
}
