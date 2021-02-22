<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Integral\Observers;

use Larva\Integral\Models\Transaction;
use Larva\Integral\Models\Withdrawal;

/**
 * 积分提现观察者
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class WithdrawalObserver
{
    /**
     * Handle the user "saving" event.
     *
     * @param Withdrawal $withdrawal
     * @return void
     */
    public function saving(Withdrawal $withdrawal)
    {
        //根据汇率计算可得多少CNY
        $withdrawal->amount = bcdiv($withdrawal->integral, settings('integral.withdrawals_cny_rate', 10),2);
    }

    /**
     * Handle the user "created" event.
     *
     * @param Withdrawal $withdrawal
     * @return void
     */
    public function created(Withdrawal $withdrawal)
    {
        $integral = -$withdrawal->integral;
        $withdrawal->transaction()->create([
            'user_id' => $withdrawal->user_id,
            'type' => Transaction::TYPE_WITHDRAWAL,
            'description' => '积分提现',
            'integral' => $integral,
            'current_integral' => bcadd($withdrawal->wallet->integral, $integral)
        ]);

        $withdrawal->transfer()->create([
            'amount' => bcmul($withdrawal->amount, 100),
            'currency' => 'CNY',
            'description' => '积分提现',
            'channel' => $withdrawal->channel,
            'metadata' => $withdrawal->metadata,
            'recipient_id' => $withdrawal->recipient
        ]);
    }
}
