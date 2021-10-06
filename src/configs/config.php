<?php

/**
 * Copyright © 2021 Moss Maurice. All rights reserved.
 * Contacts: <kreexus@yandex.ru>
 * Profile: <https://github.com/moss-maurice>
 */

use \mmaurice\parser\trustpilot\components\Logger;

return [
    'root' => 'https://de.trustpilot.com/review/',
    'source' => '',
    'logger' => Logger::IMPORTANT_MESSAGES,
    'outputFile' => 'dump',
];
