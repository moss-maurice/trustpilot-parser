<?php

/**
 * Copyright © 2021 Moss Maurice. All rights reserved.
 * Contacts: <kreexus@yandex.ru>
 * Profile: <https://github.com/moss-maurice>
 */

namespace mmaurice\parser\trustpilot\components;

class Dumper
{
    protected $dumpPath;
    protected $dumpFile;

    public function __construct($dumpFile = null)
    {
        $this->dumpPath = realpath(dirname(__FILE__) . '/../../export/');
        $this->dumpFile = !is_null($dumpFile) ? $dumpFile : 'dump';
    }

    public function set($row)
    {
        return file_put_contents($this->getDumpFile(), $row . PHP_EOL, FILE_APPEND);
    }

    public function wipe()
    {
        if (file_exists($this->getDumpFile())) {
            return unlink($this->getDumpFile());
        }

        return false;
    }

    protected function getDumpFile()
    {
        return "{$this->dumpPath}/{$this->dumpFile}.sql";
    }
}
