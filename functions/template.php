<?php

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
 * @param int $price Текущая цена
 * @param int $step Шаг ставки
 * @return int Минимальная ставка
 */
function calcNextBid(int $price, int $step): int
{
    return $price + $step;
}

/**
 * Возвращает время, прошедшее с указанной даты в человеческом формате.
 *
 * @param string $datetime Дата/время
 * @return string Строковое представление
 */
function getTimeSince(string $datetime): string
{
    $value = date_create($datetime);
    if (!$value) {
        exit('getTimeSince: не удалось преобразовать входное значение в дату/время.');
    }

    $diff = date_diff($value, date_create());
    $hours = date_interval_format($diff, '%h');
    $minutes = date_interval_format($diff, '%i');

    if ($value > date_create('1 minute ago')) {
        $result = 'Меньше минуты назад';
    } elseif ($value > date_create('1 hour ago')) {
        $result = $minutes . ' ' . getNounPluralForm(intval($minutes), 'минуту', 'минуты', 'минут') . ' назад';
    } elseif ($value > date_create('today')) {
        $result = $hours . ' ' . getNounPluralForm(intval($hours), 'час', 'часа', 'часов') . ' назад';
    } elseif ($value > date_create('yesterday')) {
        $result = 'Вчера, в ' . date_format($value, 'H:i');
    } else {
        $result = date_format($value, 'd.m.Y в H:i');
    }
    return $result;
}
