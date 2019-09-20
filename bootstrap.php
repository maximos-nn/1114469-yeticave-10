<?php
if (!session_start()) {
    exit('Не удалось запустить сессию.');
}
$sessUser = $_SESSION['user'] ?? null;

date_default_timezone_set('Europe/Moscow');
require 'functions/template.php';
require 'functions/db.php';
require 'functions/validate.php';

if (!file_exists('config.php')) {
    exit('Создайте файл config.php на основе config.sample.php и выполните настройку.');
}
$config = require 'config.php';

checkConfig($config);
