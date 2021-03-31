<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Integral\Observers;

use Larva\Integral\Models\Transaction;
use Larva\Integral\Models\Withdrawals;

/**
 * 积分提现观察者
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class WithdrawalsObserver
{
    /**
     * Handle the user "saving" event.
     *
     * @param Withdrawals $withdrawals
     * @return void
     */
    public function saving(Withdrawals $withdrawals)
    {
        //根据汇率计算可得多少CNY
        $withdrawals->amount = bcdiv($withdrawals->integral, settings('integral.withdrawals_cny_rate', 10),2);
    }

    /**
     * Handle the user "created" event.
     *
     * @param Withdrawals $withdrawals
     * @return void
     */
    public function created(Withdrawals $withdrawals)
    {
        $integral = -$withdrawals->integral;
        $withdrawals->transaction()->create([
            'user_id' => $withdrawals->user_id,
            'type' => Transaction::TYPE_WITHDRAWAL,
            'description' => '积分提现',
            'integral' => $integral,
            'current_integral' => bcadd($withdrawals->wallet->integral, $integral)
        ]);

        $withdrawals->transfer()->create([
            'amount' => bcmul($withdrawals->amount, 100),
            'currency' => 'CNY',
            'description' => '积分提现',
            'channel' => $withdrawals->channel,
            'metadata' => $withdrawals->metadata,
            'recipient_id' => $withdrawals->recipient
        ]);
    }
}
