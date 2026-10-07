<?php

namespace Tests\Unit;

use App\Console\Commands\CleanupAndReprocessAutoPointCommand;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class CleanupAndReprocessAutoPointCommandTest extends TestCase
{
    public function test_weekend_dates_are_skipped_for_auto_point_processing(): void
    {
        $command = new CleanupAndReprocessAutoPointCommand();
        $method = new \ReflectionMethod($command, 'shouldSkipAutoPointDate');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($command, Carbon::create(2026, 8, 1)));
        $this->assertTrue($method->invoke($command, Carbon::create(2026, 8, 2)));
        $this->assertFalse($method->invoke($command, Carbon::create(2026, 8, 3)));
    }
}
