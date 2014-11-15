<?php

include_once __DIR__ . '/../vendor/functions.php';

$post = $_POST;

if (!isset($post['count']) || !isset($post['queue_id']) || !isset($post['time'])) {
    sendResponse('Bad request!', true);
}

$speed = $post['count'] / $post['time'] * 1000;

$date = date('Y-m-d H:i:00');

sendQuery('INSERT INTO logs (user_id, count, time, queue_id, created_at) VALUES (?, ?, ?, ?, ?)', [$user->id, $post['count'], $post['time'], $post['queue_id'], $date]);

sendQuery('UPDATE users SET speed = ? WHERE id = ?', [(int) $speed, $user->id]);

sendResponse('Thanks for log!');
