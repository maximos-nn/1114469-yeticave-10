<?php
$isAuth = false;
$userName = '';
$sessUser = null;
if (session_status() === PHP_SESSION_NONE && session_start()) {
    $sessUser = $_SESSION['user'] ?? null;
    $isAuth = (bool)$sessUser;
    $userName = $_SESSION['user']['name'] ?? '';
    session_write_close();
}

date_default_timezone_set('Europe/Moscow');
require 'functions/template.php';
require 'functions/db.php';
require 'functions/validate.php';

if (!file_exists('config.php')) {
    exit('Создайте файл config.php на основе config.sample.php и выполните настройку.');
}
$config = require 'config.php';
