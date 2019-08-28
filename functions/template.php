<?php

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

/**
 * Возвращает корректную форму множественного числа.
 * **Ограничения: только для целых чисел**\
 * Пример использования:
 * ```
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     get_noun_plural_form(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * ```
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественнго числа
 */
function getNounPluralForm(int $number, string $one, string $two, string $many): string
{
    $number = (int) $number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
        case ($mod100 >= 11 && $mod100 <= 20):
            return $many;

        case ($mod10 > 5):
            return $many;

        case ($mod10 === 1):
            return $one;

        case ($mod10 >= 2 && $mod10 <= 4):
            return $two;

        default:
            return $many;
    }
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function includeTemplate(string $name, array $data = []): string
{
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

/**
 * Форматирует цену
 *
 * @param string $price
 *
 * @return string
 */
function formatPrice(string $price): string
{
    if (!is_numeric($price)) {
        return '';
    }
    return number_format(ceil($price), 0, ',', ' ') . ' ₽';
}

/**
 * Преобразует специальные символы в строке.
 *
 * @param string $string
 *
 * @return string
 */
function clearSpecials(string $string): string
{
    return htmlspecialchars($string);
}

/**
 * Возвращает период до указанной даты в часах и минутах.
 *
 * @param  string $date Дата из будущего
 *
 * @return string[] Массив, где первый элемент — целое количество часов до даты, а второй — остаток в минутах
 */
function getTimeUntil(string $date): array
{
    $date = date_create($date);
    if (!$date) {
        return ['hours' => '00', 'minutes' => '00'];
    }
    $diff = date_diff(date_create(), $date);
    if (date_interval_format($diff, '%r')) {
        return ['hours' => '00', 'minutes' => '00'];
    }
    $days = date_interval_format($diff, '%a');
    $hours = date_interval_format($diff, '%H');
    $minutes = date_interval_format($diff, '%I');
    if ($days) {
        return ['hours' => $days * 24 + $hours, 'minutes' => $minutes];
    }
    return ['hours' => $hours, 'minutes' => $minutes];
}

/**
 * Отображает сообщение об ошибке.
 * **Завершает выполнение скрипта.**
 *
 * @param string $errMessage Выводимое сообщение
 * @param string $header Заголовок ошибки
 * @return void
 */
function showError(string $errMessage, string $header = 'Ошибка'): void
{
    if ($errMessage === '404') {
        $errMessage = 'Данной страницы не существует на сайте.';
        $header = '404 Страница не найдена';
    }
    exit(includeTemplate(
        'error.php',
        ['error' => $errMessage, 'header' => $header]
    ));
}

/**
 * Вычисляет минимальную допустимую ставку
 *
 * @param string $price Текущая цена
 * @param string $step Шаг ставки
 * @return string Минимальная ставка
 */
function calcNextBid(string $price, string $step): string
{
    return strval(intval($price) + intval($step));
}
