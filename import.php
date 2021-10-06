<?php

/**
 * Copyright © 2021 Moss Maurice. All rights reserved.
 * Contacts: <kreexus@yandex.ru>
 * Profile: <https://github.com/moss-maurice>
 */

use \mmaurice\parser\trustpilot\components\Config;
use \mmaurice\parser\trustpilot\components\Dumper;
use \mmaurice\parser\trustpilot\components\Logger;
use \mmaurice\parser\trustpilot\components\Registry;
use \mmaurice\parser\trustpilot\components\Timer;
use \mmaurice\parser\trustpilot\Parser;

require_once realpath(dirname(__FILE__) . '/vendor/autoload.php');

$registry = new Registry;

$registry->set('timer', function () {
    return new Timer;
});

$registry->set('config', function () use ($registry) {
    $config = [];

    $configPath = realpath(dirname(__FILE__) . '/configs/config.php');

    if ($configPath) {
        $config = include $configPath;
    }

    return new Config($config);
});

$registry->set('logger', function () use ($registry) {
    return new Logger($registry->get('config')->logger);
});

$registry->set('dumper', function () use ($registry) {
    return new Dumper($registry->get('config')->outputFile);
});

if ((php_sapi_name() !== 'cli')) {
    $registry->get('logger')->set("Access denied! Only CLI-mode available");
} else {
    $registry->get('timer')->start();

    $parser = new Parser($registry);

    $registry->get('dumper')->wipe();

    $registry->get('dumper')->set("-- Reviews from https://de.trustpilot.com/ dump");
    $registry->get('dumper')->set("");
    $registry->get('dumper')->set("SET NAMES utf8;");
    $registry->get('dumper')->set("SET time_zone = '+00:00';");
    $registry->get('dumper')->set("SET foreign_key_checks = 0;");
    $registry->get('dumper')->set("SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';");
    $registry->get('dumper')->set("");
    $registry->get('dumper')->set("DROP TABLE IF EXISTS `trustpilot_reviews`;");
    $registry->get('dumper')->set("CREATE TABLE `trustpilot_reviews` (");
    $registry->get('dumper')->set("    `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Уникальный идентификатор',");
    $registry->get('dumper')->set("    `review_url` varchar(1024) NOT NULL COMMENT 'Ссылка на отзыв',");
    $registry->get('dumper')->set("    `review_date` datetime DEFAULT NULL COMMENT 'Дата отзыва',");
    $registry->get('dumper')->set("    `review_title` varchar(1024) NOT NULL COMMENT 'Заголовок отзыва',");
    $registry->get('dumper')->set("    `review_body` text NOT NULL COMMENT 'Тело отзыва',");
    $registry->get('dumper')->set("    `review_rate` smallint(1) DEFAULT NULL COMMENT 'Оценка отзыва',");
    $registry->get('dumper')->set("    `review_lang` varchar(8) NOT NULL COMMENT 'Язык отзыва',");
    $registry->get('dumper')->set("    `author_url` varchar(1024) NOT NULL COMMENT 'Ссылка на автора',");
    $registry->get('dumper')->set("    `author_name` varchar(1024) NOT NULL COMMENT 'Имя автора',");
    $registry->get('dumper')->set("    `reply_date` datetime DEFAULT NULL COMMENT 'Дата ответа',");
    $registry->get('dumper')->set("    `reply_title` varchar(1024) DEFAULT NULL COMMENT 'Заголовок ответа',");
    $registry->get('dumper')->set("    `reply_body` text COMMENT 'Тело ответа',");
    $registry->get('dumper')->set("    `created_at` datetime DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания',");
    $registry->get('dumper')->set("    `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT 'Дата обновления',");
    $registry->get('dumper')->set("    PRIMARY KEY (`id`)");
    $registry->get('dumper')->set(") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Таблица отзывов';");
    $registry->get('dumper')->set("");

    $parser->getLoot();

    $registry->get('dumper')->set("");
    $registry->get('dumper')->set("-- " . date('Y-m-d H:i:s'));
    $registry->get('dumper')->set("");

    $time = $registry->get('timer')->finish();

    $registry->get('timer')->destroy();

    $registry->get('logger')->set("Done in {$time}");

    exit;
}

exit(1);
