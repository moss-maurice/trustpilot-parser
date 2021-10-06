<?php

/**
 * Copyright © 2021 Moss Maurice. All rights reserved.
 * Contacts: <kreexus@yandex.ru>
 * Profile: <https://github.com/moss-maurice>
 */

namespace mmaurice\parser\trustpilot\components;

use \mmaurice\parser\trustpilot\helpers\CmdHelper;

class Logger
{
    const NO_MESSAGES = 0;
    const ALL_MESSAGES = 1;
    const IMPORTANT_MESSAGES = 2;
    const ERROR_MESSAGES = 3;

    const LEVEL_NORMAL = 0;
    const LEVEL_IMPORTANT = 1;
    const LEVEL_ERROR = 2;

    protected $level;

    public function __construct($level = self::ALL_MESSAGES)
    {
        $this->level = intval($level);
    }

    protected function checkMessageLevel($level)
    {
        switch ($this->level) {
            case self::ALL_MESSAGES:
                return in_array($level, [self::LEVEL_ERROR, self::LEVEL_IMPORTANT, self::LEVEL_NORMAL]);

                break;
            case self::IMPORTANT_MESSAGES:
                return in_array($level, [self::LEVEL_ERROR, self::LEVEL_IMPORTANT]);

                break;
            case self::ERROR_MESSAGES:
                return in_array($level, [self::LEVEL_ERROR]);

                break;
            case self::NO_MESSAGES:
            default:

                break;
        }

        return false;
    }

    public function set($line, $options = [], $level = self::LEVEL_NORMAL)
    {
        if ($this->checkMessageLevel($level)) {
            $rawLine = CmdHelper::textColor('yellow', date('Y-m-d H:i:s')) . CmdHelper::textColor('white', ' > ') . CmdHelper::textColor('light_cyan', $line);

            if (is_array($options) and !empty($options)) {
                foreach ($options as $key => $option) {
                    $options[$key] = CmdHelper::textColor('white', $key) . ': ' . CmdHelper::textColor('magenta', $option);
                }

                $rawLine .= CmdHelper::textColor('white', ' (' . implode('; ', array_values($options)) . ')');
            }

            fwrite(STDOUT, $rawLine . PHP_EOL);
        }
    }
}
