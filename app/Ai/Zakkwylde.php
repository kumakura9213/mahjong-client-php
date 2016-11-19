<?php

namespace App\Ai;

use App\Ai\AiBase;

/**
 * Zakkwylde
 * 
 * Zakk wyldeのような雄々しい打牌を行うAiクラス。
 * 
 * ・漢は涙を見せてはいけない。
 * →ポン・チー・カンの鳴きは行わない。
 * 
 * ・漢は正々堂々としてなければならない。
 * →必ずリーチを行う。ダマテンという選択肢はこの漢にはない。
 *
 * @author kumakura9213
 */
class Zakkwylde extends AiBase
{

    /**
     * tsumo
     * 
     * 自分のツモ時に何を行うかを計算し、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data
     * @return array 麻雀サーバーに送信するデータ
     */
    public function tsumo(\stdClass $data)
    {
        $has_possible_actions = !empty($data->possible_actions);
        if ($has_possible_actions && $data->possible_actions['type'] == 'reach') {
            // リーチできる場合、迷わずリーチを行う。
            return [
                'type'  => 'reach',
                'actor' => $this->player['id'],
            ];
        } elseif ($has_possible_actions && $data->possible_actions['type'] == 'hora') {
            // 和了できる場合、迷わず和了する。
            return [
                'type'   => 'hora',
                'actor'  => $this->player['id'],
                'pai'    => $data->possible_actions['pai'],
                'target' => $data->possible_actions['target'],
            ];
        }

        // それ以外の場合、いったんツモ切りする。
        return [
            'type'      => 'dahai',
            'actor'     => $this->player['id'],
            'pai'       => $data->pai,
            'tsumogiri' => true,
        ];
    }

    /**
     * dahai
     * 
     * 相手の打牌時に何を行うかを計算し、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data
     * @return array 麻雀サーバーに送信するデータ
     */
    public function dahai(\stdClass $data)
    {
        // TODO horaできる場合、horaする。
        return $this->none();
    }

    /**
     * reach
     * 
     * 自分のリーチ時に何を行うかを計算し、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data
     * @return array 麻雀サーバーに送信するデータ
     */
    public function reach(\stdClass $data)
    {
        // TODO cannot_dahaiでいい感じに
        $data->cannot_dahai;
        return [
            'type'  => 'dahai',
            'actor' => 'XXX',
            'pai'   => 'XXX',
            'tsumogiri' => false,
        ];
    }

    /**
     * pon
     * 
     * 自分のポン時に何を行うかを計算し、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data
     * @return array 麻雀サーバーに送信するデータ
     */
    public function pon(\stdClass $data)
    {
        return $this->none();
    }

    /**
     * chi
     * 
     * 自分のチー時に何を行うかを計算し、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data
     * @return array 麻雀サーバーに送信するデータ
     */
    public function chi(\stdClass $data)
    {
        return $this->none();
    }

    /**
     * daiminkan
     * 
     * 自分のダイミンカン時に何を行うかを計算し、麻雀サーバーに送信するデータを返却する。
     * 
     * @param \stdClass $data
     * @return array 麻雀サーバーに送信するデータ
     */
    public function daiminkan(\stdClass $data)
    {
        return $this->none();
    }
}
