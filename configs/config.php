<?php

/**
 * Copyright © 2021 Moss Maurice. All rights reserved.
 * Contacts: <kreexus@yandex.ru>
 * Profile: <https://github.com/moss-maurice>
 */

use \mmaurice\parser\trustpilot\components\Logger;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '4096M');

return [
    'source' => 'russland-visum.eu',
    'logger' => Logger::ALL_MESSAGES,
];
