<?php

require('vendor/autoload.php');

// 麻雀クライアントの作成する。
$client = new App\MahjongClient(
    'logs/play.log',
    'ws://www.logos.t.u-tokyo.ac.jp/mjai/'
);

// 対局を始める。
$client->run();

// EOF
