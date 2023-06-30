<?php

namespace T73Biz\PhpUnitWatcher;

class OS
{
    /**
     * Indicates if the process is being executed in a windows machine.
     *
     * @return bool
     */
    public static function isOnWindows()
    {
        return DIRECTORY_SEPARATOR !== '/';
    }
}
