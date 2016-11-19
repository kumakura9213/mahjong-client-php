<?php

namespace App\Ai;

use App\Config;
use App\MahjongClient;
use App\Ai\AiActionInterface;
use App\Ai\AiComputeInterface;

/**
 * AiBase
 *
 * @author kumakura9213
 */
abstract class AiBase implements AiActionInterface, AiComputeInterface
{

    /**
     * @var App\Config $config
     */
    protected $config;

    /**
     * @var App\MahjongClient $owner
     */
    protected $owner;

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
        'tehai'       => null,
    ];

    /**
     * __construct
     * 
     * Configの設定を行う。
     */
    public function __construct(MahjongClient $owner)
    {
        $this->config = Config::load('app.php');
        $this->owner  = $owner;
    }

    /**
     * action
     * 
     * 麻雀サーバーから受け取ったデータをもとに処理を行い、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data 麻雀サーバーから受け取ったJSONデータをデコードしたもの
     * @return array 麻雀サーバーに送信するデータ
     */
    public function action(\stdClass $data)
    {
        // 接続時のタイプに応じて、処理を行う。
        switch($data->type) {
            // 接続時
            case 'hello':
                return $this->join();
            // ゲーム開始時
            case 'start_game':
                $this->setPlayer($data);
                return $this->none();
            // 局開始時
            case 'start_kyoku':
                $this->setEnvironment($data, $this->player['id']);
                return $this->none();
            // 和了時・流局時・局終了時
            case 'hora':
            case 'ryukyoku':
            case 'end_kyoku':
                return $this->none();
            // ゲーム終了時
            case 'end_game':
                $this->owner->setGameEnd();
                return $this->none();
            // ツモ時
            case 'tsumo':
                return ($this->isMyActor($data->actor)) ? $this->tsumo($data) : $this->none();
            // 確認時
            case 'dahai':
                return ($this->isMyActor($data->actor)) ? $this->none() : $this->dahai($data);
            // その他
            case 'pon':
                return ($this->isMyActor($data->actor)) ? $this->pon($data) : $this->none();
            case 'chi':
                return ($this->isMyActor($data->actor)) ? $this->chi($data) : $this->none();
            case 'daiminkan':
                return ($this->isMyActor($data->actor)) ? $this->daiminkan($data) : $this->none();
            case 'reach':
                return ($this->isMyActor($data->actor)) ? $this->reach($data) : $this->none();
            case 'ankan':
            case 'kakan':
            case 'reach_accepted':
            case 'dora':
            case 'hora':
            case 'end_kyoku':
            case 'ryukyoku':
                return $this->none();
            default:
                // 想定していないものが来た場合、とりあえずnoneで返す。
                return $this->none();
        }
    }

    /**
     * setPlayer
     * 
     * player情報を設定する。
     * 
     * @param \stdClass $data 麻雀サーバーから受け取ったJSONデータをデコードしたもの
     */
    protected function setPlayer(\stdClass $data)
    {
        $this->player = [
            'id'   => $data->id,
            'name' => $this->config->get('name'),
        ];
    }

    /**
     * setEnvironment
     * 
     * environment情報を設定する。
     * 
     * @param \stdClass $data 麻雀サーバーから受け取ったJSONデータをデコードしたもの
     * @param int $id playerのid
     */
    protected function setEnvironment(\stdClass $data, int $id)
    {
        $this->environment = [
            'bakaze'      => $data->bakaze,
            'honba'       => $data->honba,
            'kyotaku'     => $data->kyotaku,
            'oya'         => $data->oya,
            'dora_marker' => $data->dora_marker,
            'tehai'       => $data->tehais[$id],
        ];
    }

    /**
     * isMyActor
     * 
     * 自分の手番かどうかを返却する。
     * 
     * @return bool true:自分の手番である、false:自分の手番でない
     */
    protected function isMyActor(int $actor)
    {
        return ($actor == $this->player['id']);
    }

    /**
     * join
     * 
     * join用のデータを麻雀サーバーに送信する。
     */
    protected function join()
    {
        return [
            'type' => 'join',
            'name' => $this->config->get('name'),
            'room' => 'default',
        ];
    }

    /**
     * none
     * 
     * none用のデータを返却する。
     * 
     * @return array none用のデータ
     */
    protected function none()
    {
        return ['type' => 'none',];
    }
}
