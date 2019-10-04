<?php

/**
 * Правило для проверки названия лота
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateLotName = function (string $value) {
    if ($value === '') {
        return 'Введите наименование лота';
    }
    if (!preg_match('/^[-а-яёa-z0-9\/.() ]+$/iu', $value)) {
        return 'В строке присутствуют недопустимые символы';
    }
    return isLengthValid($value, 1, 255) ? '' : 'Поле нужно заполнить, и оно не должно превышать 255 символов';
};

/**
 * Правило для проверки описания лота
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateLotComment = function (string $value) {
    return isLengthValid($value, 1) ? '' : 'Напишите описание лота';
};

/**
 * Правило для проверки цены лота
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateLotPrice = function (string $value) {
    if (!$value) {
        return 'Введите начальную цену';
    }
    return getIntValue($value) ? '' : 'Цена должна быть числом больше 0';
};

/**
 * Правило для проверки шага ставки
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateBidStep = function (string $value) {
    if (!$value) {
        return 'Введите шаг ставки';
    }
    return getIntValue($value) ? '' : 'Шаг ставки должен быть числом больше 0';
};

/**
 * Правило для проверки категории лота
 *
 * @param string $value Значение для проверки
 * @param array &$catIds Список допустимых вариантов категории
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
// TODO лушче не использовать use, так как функция будет зависеть от окружения.  Смотри файл Refactoring.md
$validateCategory = function (string $value) use (&$catIds) {
    return in_array($value, $catIds, true) ? '' : 'Выберите категорию';
};

/**
 * Правило для проверки даты завершения торгов
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
$validateLotExpire = function (string $value) {
    if (!$value) {
        return 'Введите дату завершения торгов';
    }
    if (!isDateValid($value)) {
        return 'Дата должна быть в формате "ГГГГ-ММ-ДД"';
    }
    return date_create($value) > date_create('tomorrow') ? '' : 'Дата должна быть больше завтрашней';
};

/**
 * Правило для проверки файла изображения лота
 *
 * @param array $data Данные о загруженном файле
 * @param string &$fileName Путь и имя корректного файла
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
// TODO смотри файл Refactoring.md
$validateImage = function (array $data) use (&$fileName) {
    if (!isset($data['error']) || is_array($data['error'])) {
        return 'Неверные параметры запроса';
    }

    $error = validateFileError($data['error']);
    if ($error) {
        return $error;
    }

    if (empty($data['tmp_name'])) {
        return 'Отсутствует имя файла';
    }

    $types = ['png' => 'image/png', 'jpg' => 'image/jpeg'];
    $ext = array_search(
        mime_content_type($data['tmp_name']),
        $types,
        true
    );
    if (!$ext) {
        return 'Неверный формат файла. Ожидалось: ' . implode(', ', array_values($types));
    }

    $file = moveFile($data['tmp_name'], $ext);
    if (!$file) {
        return 'Ошибка сохранения файла';
    }
    $fileName = $file;
    return '';
};

/**
 * Анализирует код ошибки загрузки файла.
 *
 * @param integer $error Код ошибки
 * @return string Описание ошибки или пустая строка в случае её отсутствия
 */
function validateFileError(int $error): string
{
    switch ($error) {
        case UPLOAD_ERR_OK:
            $result = '';
            break;
        case UPLOAD_ERR_NO_FILE:
            $result = 'Файл не был отправлен';
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            $result = 'Превышен допустимый размер файла';
            break;
        default:
            $result = 'Неизвестная ошибка передачи файла';
    }
    return $result;
}
