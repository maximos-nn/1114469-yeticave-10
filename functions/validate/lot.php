<?php

/**
 * Правило для проверки названия лота
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
function validateLotName(string $name): string
{
    if ($name === '') {
        return 'Введите наименование лота';
    }
    if (!preg_match('/^[-а-яёa-z0-9\/.() ]+$/iu', $name)) {
        return 'В строке присутствуют недопустимые символы';
    }
    return isLengthValid($name, 1, 255) ? '' : 'Поле нужно заполнить, и оно не должно превышать 255 символов';
}

/**
 * Правило для проверки описания лота
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
function validateLotComment(string $comment): string
{
    return isLengthValid($comment, 1) ? '' : 'Напишите описание лота';
}

/**
 * Правило для проверки цены лота
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
function validateLotPrice(string $price): string
{
    if (!$price) {
        return 'Введите начальную цену';
    }
    return getIntValue($price) ? '' : 'Цена должна быть числом больше 0';
}

/**
 * Правило для проверки шага ставки
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
function validateBidStep(string $step): string
{
    if (!$step) {
        return 'Введите шаг ставки';
    }
    return getIntValue($step) ? '' : 'Шаг ставки должен быть числом больше 0';
}

/**
 * Правило для проверки категории лота
 *
 * @param string $value Значение для проверки
 * @param array $catIds Список допустимых вариантов категории
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
function validateCategory(string $value, array $catIds): string
{
    return in_array($value, $catIds, true) ? '' : 'Выберите категорию';
}

/**
 * Правило для проверки даты завершения торгов
 *
 * @param string $value Значение для проверки
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
function validateLotExpire(string $date): string
{
    if (!$date) {
        return 'Введите дату завершения торгов';
    }
    if (!isDateValid($date)) {
        return 'Дата должна быть в формате "ГГГГ-ММ-ДД"';
    }
    return date_create($date) > date_create('tomorrow') ? '' : 'Дата должна быть больше завтрашней';
}

/**
 * Правило для проверки файла изображения лота
 *
 * @param array $data Данные о загруженном файле
 * @param array imageTypes Массив типов изображений
 * @return string Сообщение об ошибке или пустая строка при корректном значении
 */
function validateImage(array $data, array $imageTypes): string
{
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

    if (!getFileExtension($data['tmp_name'], $imageTypes)) {
        return 'Неверный формат файла. Ожидалось: ' . implode(', ', array_values($imageTypes));
    }

    return '';
}

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

/**
 * Производит проверку полей формы добавления лота.
 *
 * @param array $formData Данные формы
 * @param array $categoryIds Идентификаторы допустимых категорий нового лота
 * @param array $fileData Загруженный файл изображения нового лота
 * @param array $imageTypes Допустимые типы изображений
 * @return array Массив ошибок валидации, может быть пустым
 */
function validateLotForm(array $formData, array $categoryIds, array $fileData, array $imageTypes): array
{
    $errors = [];
    if ($error = validateCategory($formData['category'] ?? '', $categoryIds)) {
        $errors['category'] = $error;
    }
    if ($error = validateLotName($formData['lot-name'] ?? '')) {
        $errors['lot-name'] = $error;
    }
    if ($error = validateLotComment($formData['message'] ?? '')) {
        $errors['message'] = $error;
    }
    if ($error = validateLotPrice($formData['lot-rate'] ?? '')) {
        $errors['lot-rate'] = $error;
    }
    if ($error = validateBidStep($formData['lot-step'] ?? '')) {
        $errors['lot-step'] = $error;
    }
    if ($error = validateLotExpire($formData['lot-date'] ?? '')) {
        $errors['lot-date'] = $error;
    }
    if ($error = validateImage($fileData, $imageTypes)) {
        $errors['lot-img'] = $error;
    }
    return $errors;
}
