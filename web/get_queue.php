<?php

include_once __DIR__ . '/../vendor/functions.php';

//sendResponse('NO!', 1);

// Get first unchecked md5 hash
$hash = getOne('SELECT * FROM md5 WHERE result IS NULL ORDER BY id ASC LIMIT 1;');

if (!$hash) {
    sendResponse("Извините, но у нас нет для Вас работы!", true);
}

$queue = getOne("
    SELECT q.* FROM queue q
    INNER JOIN md5 as m ON q.md5_id = m.id
    WHERE m.id = ?
    AND q.updated_at < NOW() - INTERVAL 5 MINUTE
    AND q.done = 0
    LIMIT 1;
", [$hash->id]);

$time = $time = date('Y-m-d H:i:s');

if (!$queue) {
    $lastQueue = getOne("
            SELECT * FROM queue q
            INNER JOIN md5 as m ON q.md5_id = m.id
            WHERE m.id = ?
            ORDER BY q.id DESC
            LIMIT 1;
        ", [$hash->id]);

    $begin = $lastQueue ? toString((int)toNumber($lastQueue->end) + 1) : toString(0);
    $end = toString((int)toNumber($begin) + $speed);

    $id = sendQuery('INSERT INTO queue (md5_id, begin, end, created_at, updated_at, user_id) VALUES (?,?,?,?,?,?)', [$hash->id, $begin, $end, $time, $time, $user->id]);
    $queue = getOne('SELECT * FROM queue WHERE id = ?', [$id]);
} else {
    sendQuery('UPDATE queue SET updated_at = ? WHERE id = ?', [$time, $queue->id]);
}


sendResponse(['md5' => $hash, 'queue' => $queue]);