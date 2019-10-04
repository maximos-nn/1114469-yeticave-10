<?php

/**
 * Проверяет, является ли переданное значение корректным натуральным числом.
 * Для отрицательных и некорректных значений вернет null.
 * Но нас это устраивает, т.к. в схеме БД идентификаторы объявлены
 * как UNSIGNED INT, и все числовые поля являются натуральными.
 *
 * @param mixed $value Значение для анализа
 * @return int|null Возвращает корректное значение или null
 */
function getIntValue($value): ?int
{
    if (!preg_match('/^[1-9][0-9]*$/u', strval($value))) {
        return null;
    }
    return (int)$value;
}

/**
 * Проверяет корректность целочисленного значения в массиве по его ключу.
 *
 * @param array $array Масив со значением
 * @param string $key Ключ значения
 * @return integer|null Возвращает корректное значение или null
 */
function getIntParam(array $array, string $key): ?int
{
    return getIntValue($array[$key] ?? null);
}

/**
 * Удаляет пробелы в начале и конце каждого элемента массива.
 *
 * @param string[] $data Массив строк для обработки
 * @return string[] Массив строк
 */
function trimItems(array $data): array
{
    return array_map(
        function (string $item) {
            return trim($item);
        },
        $data
    );
}
