<?php

/**
 * Copyright © 2021 Moss Maurice. All rights reserved.
 * Contacts: <kreexus@yandex.ru>
 * Profile: <https://github.com/moss-maurice>
 */

namespace mmaurice\parser\trustpilot\helpers;

use \PHP_Parallel_Lint\PhpConsoleColor\ConsoleColor;

class CmdHelper
{
    public static function textColor($color, $text)
    {
        return (new ConsoleColor)->apply($color, $text);
    }

    public static function logLine($string = '')
    {
        return self::drawLine(self::textColor('white', date('Y-m-d H:i:s') . ' > ') . $string);
    }

    public static function drawLine($string = '')
    {
        return self::drawString($string . PHP_EOL);
    }

    public static function drawString($string = '')
    {
        echo $string;

        return true;
    }
}
