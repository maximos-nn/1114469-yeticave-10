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

/**
 * Возвращает расширение файла по его MIME-типу.
 * Ожидает на входе ассоциативный массив, ключи которого представляют собой
 * строку расширения файла, а значения - MIME-типы файлов.
 *
 * @param string $fileName Путь анализируемого файла
 * @param array $types Массив расширение - MIME-тип
 * @return string Расширение файла или пустая строка в случае несоответствия ни одному типу
 */
function getFileExtension(string $fileName, array $types): string
{
    return (string)array_search(
        mime_content_type($fileName),
        $types,
        true
    );
}
