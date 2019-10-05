<?php

/**
 * Проверяет вхождение длины строки в указанный диапазон.
 *
 * @param string $str Строка
 * @param integer $min Минимальная длина
 * @param integer $max Максимальная длина
 * @return boolean
 */
function isLengthValid(string $str, int $min = null, int $max = null): bool
{
    if ($min < 0 || $max < 0 || $max && $min > $max) {
        exit('isLengthValid: Недопустимые параметры.');
    }

    $len = mb_strlen($str, 'UTF-8');

    if ($min && $len < $min) {
        return false;
    }

    if ($max && $len > $max) {
        return false;
    }
    return true;
}

/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'.
 * Примеры использования:\
 * is_date_valid('2019-01-01'); // true\
 * is_date_valid('2016-02-29'); // true\
 * is_date_valid('2019-04-31'); // false\
 * is_date_valid('10.10.2010'); // false\
 * is_date_valid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function isDateValid(string $date): bool
{
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
}
