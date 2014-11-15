<?php

include_once __DIR__ . '/../vendor/functions.php';

$post = $_POST;

sendQuery('UPDATE queue SET done = 1 WHERE id = ? AND begin = ? AND end = ?', [$post['queue']['id'], $post['queue']['begin'], $post['queue']['end']]);

sendResponse('Thx :(');