<?php

namespace App\Ai;

/**
 * AiActionInterface
 *
 * @author kumakura9213
 */
interface AiActionInterface
{

    /**
     * action
     * 
     * 麻雀サーバーから受け取ったデータをもとに処理を行い、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data 麻雀サーバーから受け取ったJSONデータをデコードしたもの
     * @return array 麻雀サーバーに送信するデータ
     */
    public function action(\stdClass $data);
}
