<?php

include_once __DIR__ . '/../vendor/functions.php';

$post = $_POST;

if (!isset($post['md5']) || !isset($post['md5']['id']) || !isset($post['md5']['md5'] )|| !isset($post['password'])) {
    sendResponse('Bad request!', true);
}

if ($post['md5']['md5'] != md5($post['password'])) {
    sendResponse('md5 != md5(password)!', true);
}

$md5 = getOne('SELECT * FROM md5 WHERE id = ?', [$post['md5']['id']]);

if (!$md5 || $md5->md5 != $post['md5']['md5']) {
    sendResponse('Bad request! your md5 != our md5!', true);
}

sendQuery('UPDATE md5 SET result = ? WHERE id = ?', [$post['password'], $post['md5']['id']]);

sendResponse('Thx!');