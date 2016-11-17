<?php

namespace App;

use WebSocket\Client;
use WebSocket\ConnectionException;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * MahjongClient
 *
 * @author kumakura9213
 */
class MahjongClient
{

    /**
     * @var App\Config $config
     */
    protected $config;

    /**
     * @var Monolog\Logger $logger
     */
    protected $logger;

    /**
     * @var WebSocket\Client $websocket_clinet
     */
    protected $websocket_clinet;

    /**
     * @var string $url 接続先
     */
    protected $url;

    /**
     * @var bool $is_game_end 対局終了フラグ(true:対局終了、false:対局中)
     */
    protected $is_game_end = false;

    /**
     * @var array $player
     */
    protected $player = [
        'id'   => null,
        'name' => null,
    ];

    /**
     * @var array $environment
     */
    protected $environment = [
        'bakaze'      => null,
        'honba'       => null,
        'kyotaku'     => null,
        'oya'         => null,
        'dora_marker' => null,
    ];

    /**
     * __construct
     * 
     * LoggerとWebSocketClientの生成を行う。
     * 
     * @param string $logging_path ログ出力先
     * @param string $url 接続先
     */
    public function __construct(string $logging_path, string $url)
    {
        $this->config = Config::load('app.php');
        $this->url = $url;
        $this->logger = new Logger('mahjong');
        $this->logger->pushHandler(new StreamHandler($logging_path, Logger::DEBUG));
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        $this->websocket_clinet = new Client($url);
    }

    /**
     * run
     * 
     * 麻雀サーバーに接続し、対局を行う。
     */
    public function run()
    {
        $this->logger->debug('run start.');
        while (!$this->is_game_end) {
            try {
                $data_json = $this->websocket_clinet->receive();

                // 受信できなかった場合、1秒後に再度受信する。
                if (empty($data_json)) {
                    sleep(1);
                    continue;
                }

                $this->logger->debug('->'.$data_json);

                // 受け取ったデータをログ出力した後、データにもとづき行動を行う。
                $this->action(json_decode($data_json));
            } catch (ConnectionException $e) {
                // 切れた場合、1秒後に再接続する。
                $this->logger->debug('disconnect...');
                sleep(1);
                $this->websocket_clinet = new Client($this->url);
            }
        }
        $this->logger->debug('run end.');
    }

    /**
     * action
     * 
     * 麻雀サーバーから受け取ったデータをもとに処理を行う。
     * 
     * @param \stdClass $data 麻雀サーバーから受け取ったJSONデータをデコードしたもの
     * @return bool true:処理を終了する。false:処理を続行する。
     */
    public function action(\stdClass $data)
    {
        // 接続時のタイプに応じて、処理を行う。
        switch($data->type) {
            // 接続時
            case 'hello':
                $this->send([
                    'type' => 'join',
                    'name' => $this->config->get('name'),
                    'room' => 'default',
                ]);
                break;
            // ゲーム開始時
            case 'start_game':
                $this->player['id']   = $data->id;
                $this->player['name'] = $this->config->get('name');
                $this->none();
                break;
            // 局開始時
            case 'start_kyoku':
                $this->environment = [
                    'bakaze'      => $data->bakaze,
                    'honba'       => $data->honba,
                    'kyotaku'     => $data->kyotaku,
                    'oya'         => $data->oya,
                    'dora_marker' => $data->dora_marker,
                ];
                $this->none();
                break;
            // ツモ時
            case 'tsumo':
                if ($data->actor == $this->player['id']) {
                    // 自分のツモ時
                    // TODO ツモ切りマシーン、、、
                    $this->send([
                        'type'      => 'dahai',
                        'actor'     => $this->player['id'],
                        'pai'       => $data->pai,
                        'tsumogiri' => true,
                    ]);
                } else {
                    // 相手のツモ時
                    $this->none();
                }
                break;
            // 確認時
            case 'dahai':
                if ($data->actor == $this->player['id']) {
                    // 自分の確認時
                    $this->none();
                } else {
                    // 相手の確認時
                    $this->none();
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
                // TODO 一旦、何もしない。
                $this->none();
                break;
            case 'end_game':
                // ゲームが終了したら、いったん終了する。
                $this->none();
                $this->is_game_end = true;
                return;
            default:
                // 想定していないものが来た場合、とりあえずnoneで返す。
                $this->none();
                break;
        }
    }

    /**
     * send
     * 
     * 引数の配列をJSON形式にエンコードとして、麻雀サーバーに送信する。
     * 
     * @param array $data
     */
    public function send(array $data)
    {
        $data_json = json_encode($data);
        $this->logger->debug('<-'.$data_json);
        $this->websocket_clinet->send(json_encode($data));
    }

    /**
     * none
     * 
     * noneのデータを麻雀サーバーに送信する。
     */
    public function none()
    {
        $this->send(['type' => 'none',]);
    }
}
