<?php

/**
 * Выполняет базовые проверки конфигурации проекта.
 *
 * @param mixed $config Конфигурация проекта
 * @return void
 */
function checkConfig($config): void
{
    if (!($config['db'] ?? null) || !is_array($config['db'])) {
        exit('В конфигурации не заданы настройки подключения к БД.');
    }

    if (!($lotsPerPage = $config['lots_per_page'] ?? null)) {
        exit('В конфигурации не задано количество лотов на странице.');
    }
    if (intval($lotsPerPage) <= 0) {
        exit('В конфигурации задано некорректное количество лотов на странице.');
    }

    if (!($config['image_types'] ?? null) || !is_array($config['image_types'])) {
        exit('В конфигурации не заданы допустимые форматы изображений.');
    }

    if (!($config['smtp'] ?? null) || !is_array($config['smtp'])) {
        exit('В конфигурации не заданы настройки подключения к SMTP-серверу.');
    }

    $smtp = $config['smtp'];
    if (
        !isset(
            $smtp['host'],
            $smtp['port'],
            $smtp['user'],
            $smtp['password'],
            $smtp['sender']
        )
        || !array_key_exists('encryption', $smtp)
        ) {
        exit('В конфигурации SMTP заданы не все необходимые параметры.');
    }

    if (!($config['base_url'] ?? null)) {
        exit('В конфигурации не задан URL сайта.');
    }
}
