<?php

namespace App\Ai;

/**
 * AiComputeInterface
 *
 * @author kumakura9213
 */
interface AiComputeInterface
{

    /**
     * tsumo
     * 
     * 自分のツモ時に何を行うかを計算し、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data
     * @return array 麻雀サーバーに送信するデータ
     */
    public function tsumo(\stdClass $data);

    /**
     * dahai
     * 
     * 相手の打牌時に何を行うかを計算し、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data
     * @return array 麻雀サーバーに送信するデータ
     */
    public function dahai(\stdClass $data);

    /**
     * reach
     * 
     * 自分のリーチ時に何を行うかを計算し、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data
     * @return array 麻雀サーバーに送信するデータ
     */
    public function reach(\stdClass $data);

    /**
     * pon
     * 
     * 自分のポン時に何を行うかを計算し、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data
     * @return array 麻雀サーバーに送信するデータ
     */
    public function pon(\stdClass $data);

    /**
     * chi
     * 
     * 自分のチー時に何を行うかを計算し、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data
     * @return array 麻雀サーバーに送信するデータ
     */
    public function chi(\stdClass $data);

    /**
     * daiminkan
     * 
     * 自分のダイミンカン時に何を行うかを計算し、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data
     * @return array 麻雀サーバーに送信するデータ
     */
    public function daiminkan(\stdClass $data);
}
