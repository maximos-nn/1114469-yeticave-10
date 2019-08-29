<?php

/**
 * Проверяет, является ли переданное значение корректным идентификатором.
 * Для отрицательных значений вернет false.
 * Но нас это устраивает, т.к. в схеме БД идентификаторы объявлены
 * как UNSIGNED INT.\
 * isValidInt(23); // bool(true)\
 * isValidInt('23'); // bool(true)\
 * isValidInt('2.3'); // bool(false)\
 * isValidInt('2e3'); // bool(false)\
 * isValidInt('-23'); // bool(false)\
 * isValidInt(-23); // bool(false)\
 * isValidInt(0); // bool(true)\
 * isValidInt(0.0); // bool(true)\
 * isValidInt('0.0'); // bool(false)\
 * isValidInt('-0.0'); // bool(false)\
 * isValidInt(-0.0); // bool(false)\
 * isValidInt(''); // bool(false)\
 * isValidInt(null); // bool(false)\
 * isValidInt('null'); // bool(false)\
 * isValidInt('test'); // bool(false)\
 * isValidInt('test3'); // bool(false)\
 * isValidInt('2test3'); // bool(false)\
 * isValidId('true'); // bool(false)\
 * isValidId('false'); // bool(false)\
 * isValidId(false); // bool(false)\
 * isValidId(true); // bool(true)
 *
 * @param mixed $input Значение для проверки
 * @return boolean
 */
function isValidId($input): bool
{
    // Первый вариант
    return ctype_digit(strval($input));

    // Второй вариант
    // return ($result = filter_var($input, FILTER_VALIDATE_INT)) !== false && $result > 0;
}
