<?php

require_once __DIR__.'/../config/paths.php';
require_once __DIR__.'/../config/server.php';

function throttle($ident, $function, $args, $interval) {
    global $data_path, $_CONFIG;
    $throttle_dir = "{$data_path}throttle/";
    if (!is_dir($throttle_dir)) {
        mkdir($throttle_dir, 0777, true);
    }
    $throttle_file = "{$throttle_dir}{$ident}.json";
    $throttling = ['last-call' => 0];
    if (is_file($throttle_file)) {
        $throttling_content = json_decode(file_get_contents($throttle_file), true);
        if ($throttling_content) {
            $throttling = $throttling_content;
        }
    }
    $now = microtime(true);
    if ($throttling['last-call'] < $now - $interval || $_CONFIG->unlimited_cron) {
        $throttling['last-call'] = $now;
        file_put_contents($throttle_file, json_encode($throttling));
        call_user_func_array($function, $args);
    } else {
        http_response_code(429);
        echo "HTTP Error 429 Too Many Requests";
    }
}
