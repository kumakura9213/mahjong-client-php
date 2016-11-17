<?php

require('vendor/autoload.php');

// configを読み込む。
$config = App\Config::load('app.php');

// 麻雀クライアントの作成する。
$client = new App\MahjongClient(
    $config->get('logging_path'),
    $config->get('url')
);

// 対局を始める。
$client->run();

// EOF
