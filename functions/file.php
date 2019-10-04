<?php

/**
 * Сохраняет загруженный временный файл.
 * Возвращает путь к сохраненному файлу или пустую строку в случае неудачи.
 *
 * @param string $tmpFileName Временный файл
 * @param string $ext Расширение для нового файла
 * @return string Сохраненный файл
 */
function moveFile(string $tmpFileName, string $ext): string
{
    $newFileName = './uploads/' . uniqid() . '.' . $ext;
    if (!move_uploaded_file($tmpFileName, $newFileName)) {
        return '';
    }
    return $newFileName;
}
