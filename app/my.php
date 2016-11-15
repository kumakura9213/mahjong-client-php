<?php
require('../vendor/autoload.php');

use WebSocket\Client;

// TODO configで指定できるようにする。
$url = "ws://www.logos.t.u-tokyo.ac.jp/mjai/";

// WebSocketのクライアントを作成する。
$client = new Client($url);

// TODO Applicationクラスを真面目に作る。
$player = [
    'id'   => null,
    'name' => 'Zakk Wylde',
];
$environment = [
    'bakaze'      => null,
    'honba'       => null,
    'kyotaku'     => null,
    'oya'         => null,
    'dora_marker' => null,
];

// ポーリング
while(true) {
    try {
        // 受信
        $json = $client->receive();
        if (empty($json)) {
            sleep(1);
            continue;
        }
        echo $json."\n";
        $data = json_decode($json);;

        // 接続時のタイプに応じて、処理を行う。
        switch($data->type) {
            // 接続時
            case 'hello':
                $client->send(json_encode([
                    'type' => 'join',
                    'name' => $player['name'],
                    'room' => 'default',
                ]));
                break;
            // ゲーム開始時
            case 'start_game':
                $player['id'] = $data->id;
                $client->send(json_encode([
                    'type' => 'none',
                ]));
                break;
            // 局開始時
            case 'start_kyoku':
                $environment = [
                    'bakaze'      => $data->bakaze,
                    'honba'       => $data->honba,
                    'kyotaku'     => $data->kyotaku,
                    'oya'         => $data->oya,
                    'dora_marker' => $data->dora_marker,
                ];
                $client->send(json_encode([
                    'type' => 'none',
                ]));
                break;
            // ツモ時
            case 'tsumo':
                if ($data->actor == $player['id']) {
                    // 自分のツモ時
                    // TODO ツモ切りマシーン、、、
                    $client->send(json_encode([
                        'type'      => 'dahai',
                        'actor'     => $player['id'],
                        'pai'       => $data->pai,
                        'tsumogiri' => true,
                    ]));
                } else {
                    // 相手のツモ時
                    $client->send(json_encode([
                        'type' => 'none',
                    ]));
                }
                break;
            // 確認時
            case 'dahai':
                if ($data->actor == $player['id']) {
                    // 自分の確認時
                    $client->send(json_encode([
                        'type' => 'none',
                    ]));
                } else {
                    // 相手の確認時
                    $client->send(json_encode([
                        'type' => 'none',
                    ]));
                }
                break;
            // その他
            case 'pon':
            case 'chi':
            case 'ankan':
            case 'kakan':
            case 'daiminkan':
            case 'reach':
            case 'reach_accepted':
            case 'dora':
            case 'hora':
            case 'end_kyoku':
            case 'ryukyoku':
            case 'end_game':
                // TODO 一旦、何もしない。
                $client->send(json_encode([
                    'type' => 'none',
                ]));
                break;
            default:
                // 想定していないものが来た場合、とりあえずnoneで返す。
                $client->send(json_encode([
                    'type' => 'none',
                ]));
                break;
        }

        // 1秒待機
        sleep(1);
    } catch(WebSocket\ConnectionException $e) {
        // 切れた場合、5秒後に再接続
        echo $e->getMessage()."\n";
        sleep(5);
        $client = new Client($url);
    }
}
// EOF
