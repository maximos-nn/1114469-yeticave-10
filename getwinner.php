<?php
require_once 'bootstrap.php';
require_once 'vendor/autoload.php';

$dbConnection = dbConnect($config['db']);

$winners = getWinners($dbConnection);

dbClose($dbConnection);

if ($winners) {
    $transport = new Swift_SmtpTransport(
        $config['smtp']['host'] ?? null,
        $config['smtp']['port'] ?? null,
        $config['smtp']['encryption'] ?? null
    );
    $transport -> setUsername($config['smtp']['user'] ?? null);
    $transport -> setPassword($config['smtp']['password'] ?? null);

    $mailer = new Swift_Mailer($transport);

    $message = new Swift_Message('Ваша ставка победила');
    $message -> setFrom($config['smtp']['sender'] ?? null);

    $failedRecipients = [];
    foreach ($winners as $winner) {
        $message -> setTo($winner['email']);
        $proto = ('on' === ($_SERVER['HTTPS'] ?? null) ? 'https' : 'http') . '://';
        $recipient = [
            'userName' => $winner['name'],
            'lotUrl' => $proto . $_SERVER['SERVER_NAME'] . '/lot.php?id=' . $winner['lotId'],
            'title' => $winner['title'],
            'bidsUrl' => $proto . $_SERVER['SERVER_NAME'] . '/bids.php'
        ];
        $message -> setBody(
            includeTemplate('email.php', $recipient),
            'text/html'
        );

        if (! $mailer -> send($message)) {
            array_push($failedRecipients, $winner['email']);
        }
    }

    // Выводим $failedRecipients в лог.
}
