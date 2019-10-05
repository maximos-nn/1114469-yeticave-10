<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Отправляет сообщения победителям торгов.
 * В качестве конфигурации ожидается ассоциативный массив со следующими ключами:
 * - host
 * - port
 * - encryption
 * - user
 * - password
 * - sender
 * - base_url
 *
 * @param array $config Настройки для отправки сообщений
 * @param array $winners Массив победителей
 * @return void
 */
function sendMailToWinners(array $config, array $winners): void
{
    $transport = getMailTransport($config);

    $mailer = new Swift_Mailer($transport);

    $message = getMailMessage($config['sender'], 'Ваша ставка победила');

    $failedRecipients = [];
    foreach ($winners as $winner) {
        setMailMessageData($message, $winner, $config['base_url']);

        if (!$mailer->send($message)) {
            $failedRecipients[] = $winner['email'];
        }
    }

    // Выводим $failedRecipients в лог.
}

/**
 * Создает объект SMTP-транспорта и задает его параметры.
 *
 * @param array $config Массив с конфигурацией подключения
 * @return void
 */
function getMailTransport(array $config): Swift_SmtpTransport
{
    $transport = new Swift_SmtpTransport(
        $config['host'],
        $config['port'],
        $config['encryption']
    );
    $transport->setUsername($config['user']);
    $transport->setPassword($config['password']);
    return $transport;
}

/**
 * Создает объект сообщения.
 *
 * @param string $sender Отправитель сообщения
 * @param string $subject Тема сообщения
 * @return void
 */
function getMailMessage(string $sender, string $subject): Swift_Message
{
    $message = new Swift_Message($subject);
    $message->setFrom($sender);
    return $message;
}

/**
 * Формирует тело сообщения и задаёт получателя на основе данных о победителе торгов.
 *
 * @param Swift_Message $message Объект сообщения
 * @param array $winner Массив с информацией о победителе торгов
 * @param string $baseUrl Адрес сайта дл формирования корретных ссылок
 * @return void
 */
function setMailMessageData(Swift_Message $message, array $winner, string $baseUrl): void
{
    $message->setTo($winner['email']);
    $recipient = [
        'userName' => $winner['name'],
        'lotUrl' => $baseUrl . '/lot.php?id=' . $winner['lotId'],
        'title' => $winner['title'],
        'bidsUrl' => $baseUrl . '/bids.php'
    ];
    $message->setBody(
        includeTemplate('email.php', $recipient),
        'text/html'
    );
}
