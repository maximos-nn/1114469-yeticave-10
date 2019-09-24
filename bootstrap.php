<?php
if (!session_start()) {
    exit('Не удалось запустить сессию.');
}
$sessUser = $_SESSION['user'] ?? null;

date_default_timezone_set('Europe/Moscow');
require __DIR__ . '/functions/template.php';
require __DIR__ . '/functions/db.php';
require __DIR__ . '/functions/validate.php';

if (!file_exists('config.php')) {
    exit('Создайте файл config.php на основе config.sample.php и выполните настройку.');
}
$config = require __DIR__ . '/config.php';

checkConfig($config);
