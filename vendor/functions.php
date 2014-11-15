<?php

/* Подключение к базе данных ODBC, используя вызов драйвера */
$dsn = 'mysql:dbname=PP;host=localhost';
$user = 'root';
$password = '';

try {
    $dbh = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    $message = 'Подключение не удалось: ' . $e->getMessage();
    sendResponse($message, true);
}

session_start();

$session = session_id();
$agent = $_SERVER['HTTP_USER_AGENT'];

$user = getOne('SELECT * FROM users WHERE session = ?', [$session]);

if (!$user) {
    $id = sendQuery('INSERT INTO users (session, agent) VALUES (?, ?)', [$session, $agent]);
    $user = getOne('SELECT * FROM users WHERE id = ?', [$id]);
} elseif ($user->agent != $agent) {
    sendQuery('UPDATE users SET agent = ? WHERE session = ?', [$agent, $session]);
}

$speed = $user->speed;

if (!$speed) {
    $speed = 10000;
}

function sendResponse($message, $isError = false)
{
    $response = [
        'status' => $isError ? 'ERROR' : 'OK',
        'body' => $message
    ];

    header('Content-Type: application/json');
    die(json_encode($response));
}

function sendQuery($query, $parameters = [])
{
    global $dbh;
    $stmt = $dbh->prepare($query, $parameters);
    $stmt->execute($parameters);

    return $dbh->lastInsertId();
}

function getOne($query, $parameters = [])
{
    global $dbh;
    $stmt = $dbh->prepare($query, $parameters);
    $stmt->execute($parameters);

    return $stmt->fetchObject();
}

function toString($number)
{
    return base_convert($number, 10, 36);
}

function toNumber($string)
{
    return base_convert($string, 36, 10);
}
