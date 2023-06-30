<?php

namespace T73Biz\PhpUnitWatcher\Test;

use PHPUnit\Framework\TestCase;
use T73Biz\PhpUnitWatcher\WatcherCommand;

class WatcherCommandTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $command = new WatcherCommand();

        $this->assertInstanceOf(WatcherCommand::class, $command);
    }
}
