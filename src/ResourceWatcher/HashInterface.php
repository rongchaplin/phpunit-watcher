<?php

namespace T73Biz\PhpUnitWatcher\ResourceWatcher;

interface HashInterface
{
    /**
     * Calculates the hash of a file.
     *
     * @param string $filepath
     *
     * @return string Returns a string containing the calculated message digest.
     */
    public function hash(string $filepath): string;
}
