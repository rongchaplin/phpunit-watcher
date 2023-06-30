<?php

namespace T73Biz\PhpUnitWatcher;

use Clue\React\Stdio\Stdio;
use React\EventLoop\Factory;
use React\EventLoop\Loop;
use React\Stream\ThroughStream;
use T73Biz\PhpUnitWatcher\Screens\Phpunit;
use Symfony\Component\Finder\Finder;
use T73Biz\PhpUnitWatcher\ResourceWatcher\Crc32ContentHash;
use T73Biz\PhpUnitWatcher\ResourceWatcher\ResourceCacheMemory;
use T73Biz\PhpUnitWatcher\ResourceWatcher\ResourceWatcher;

class Watcher
{
    /** @var \Symfony\Component\Finder\Finder */
    protected Finder $finder;

    /** @var \React\EventLoop\ExtEvLoop */
    protected $loop;

    /** @var \T73Biz\PhpUnitWatcher\Terminal */
    protected Terminal $terminal;

    /** @var array */
    protected array $options;

    public function __construct(Finder $finder, array $options)
    {
        $this->finder = $finder;

        $this->loop = Loop::get();

        $this->terminal = new Terminal($this->buildStdio());

        $this->options = $options;
    }

    public function startWatching()
    {
        $this->terminal->displayScreen(new Phpunit($this->options), false);

        $watcher = new ResourceWatcher(new ResourceCacheMemory(), $this->finder, new Crc32ContentHash());

        $this->loop->addPeriodicTimer(1 / 4, function () use ($watcher) {
            if (! $this->terminal->isDisplayingScreen(Phpunit::class)) {
                return;
            }

            if ($watcher->findChanges()->hasChanges()) {
                $this->terminal->refreshScreen();
            }
        });

        $this->loop->run();
    }

    protected function buildStdio()
    {
        $output = null;

        if (OS::isOnWindows()) {
            // Interaction on windows is currently not supported
            fclose(STDIN);

            // Simple fix for windows compatibility since we don't write a lot of data at once
            // https://github.com/clue/reactphp-stdio/issues/83#issuecomment-546678609
            $output = new ThroughStream(static function ($data) {
                echo $data;
            });
        }

        return new Stdio($this->loop, null, $output);
    }
}
