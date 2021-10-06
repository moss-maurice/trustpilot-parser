<?php

/**
 * Copyright © 2021 Moss Maurice. All rights reserved.
 * Contacts: <kreexus@yandex.ru>
 * Profile: <https://github.com/moss-maurice>
 */

namespace mmaurice\parser\trustpilot\components;

class Timer
{
    const DEFAULT_TIMER = 'main';

    protected static $timers = [];

    public function start($name = self::DEFAULT_TIMER)
    {
        self::$timers[$name] = microtime(true);
    }

    public function finish($name = self::DEFAULT_TIMER, $round = 2)
    {
        if (array_key_exists($name, self::$timers)) {
            return round(microtime(true) - self::$timers[$name], $round) . ' sec.';
        }

        return false;
    }

    public function destroy($name = self::DEFAULT_TIMER)
    {
        if (array_key_exists($name, self::$timers)) {
            unset(self::$timers[$name]);

            return true;
        }

        return false;
    }
}
