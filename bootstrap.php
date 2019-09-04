<?php
session_start();
$is_auth = $_SESSION['user'] ?? null;
$user_name = $_SESSION['user']['name'] ?? '';

date_default_timezone_set('Europe/Moscow');
require 'functions/template.php';
require 'functions/db.php';
require 'functions/validate.php';

if (!file_exists('config.php')) {
    exit('Создайте файл config.php на основе config.sample.php и выполните настройку.');
}
$config = require 'config.php';
