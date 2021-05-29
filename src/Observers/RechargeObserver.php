<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare (strict_types=1);

namespace Larva\Integral\Observers;

use Larva\Integral\Models\Recharge;

/**
 * 积分充值观察者
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class RechargeObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param Recharge $recharge
     * @return void
     */
    public function saving(Recharge $recharge)
    {
        //计算可得积分
        $recharge->integral = bcdiv(bcdiv($recharge->amount, 100), settings('integral.cny_rate', 1));
    }

    /**
     * Handle the user "created" event.
     *
     * @param Recharge $recharge
     * @return void
     * @throws \Yansongda\Pay\Exceptions\InvalidGatewayException
     */
    public function created(Recharge $recharge)
    {
        $recharge->charge()->create([
            'user_id' => $recharge->user_id,
            'amount' => $recharge->amount,
            'channel' => $recharge->channel,
            'subject' => trans('integral.integral_recharge'),
            'body' => trans('integral.integral_recharge'),
            'client_ip' => $recharge->client_ip,
            'type' => $recharge->type,//交易类型
        ]);
    }
}
