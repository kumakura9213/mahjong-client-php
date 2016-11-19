<?php

namespace App;

use App\Ai\Zakkwylde;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use WebSocket\Client;
use WebSocket\ConnectionException;

/**
 * MahjongClient
 *
 * @author kumakura9213
 */
class MahjongClient
{

    /**
     * @var Monolog\Logger $logger
     */
    protected $logger;

    /**
     * @var WebSocket\Client $websocket_clinet
     */
    protected $websocket_clinet;

    /**
     * @var bool App\Ai\AiActionInterface $ai
     */
    protected $ai;

    /**
     * @var string $url 接続先
     */
    protected $url;

    /**
     * @var bool $is_game_end 対局終了フラグ(true:対局終了、false:対局中)
     */
    protected $is_game_end = false;

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
        $this->url = $url;
        $this->logger = new Logger('mahjong');
        $this->logger->pushHandler(new StreamHandler($logging_path, Logger::DEBUG));
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));
        $this->websocket_clinet = new Client($url);
        $this->ai = new Zakkwylde($this);
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

                // 受け取ったデータをログ出力する。
                $this->logger->debug('->'.$data_json);

                // データにもとづき行動を行い、その結果を送信する。
                $this->send(
                    $this->ai->action(json_decode($data_json))
                );
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
     * send
     * 
     * 引数の配列をJSON形式にエンコードとして、麻雀サーバーに送信する。
     * 
     * @param array $data
     */
    protected function send(array $data)
    {
        $data_json = json_encode($data);
        $this->logger->debug('<-'.$data_json);
        $this->websocket_clinet->send(json_encode($data));
    }

    /**
     * setGameEnd
     * 
     * 対局終了フラグをtrueにする。
     */
    public function setGameEnd()
    {
        $this->is_game_end = true;
    }
}
