<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Integral\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Larva\Integral\Models\Recharge;
use Larva\Transaction\Events\ChargeFailure;

/**
 * Class ChargeFailureListener
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class ChargeFailureListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ChargeFailure $event
     * @return void
     */
    public function handle(ChargeFailure $event)
    {
        if ($event->charge->order instanceof Recharge) {//积分充值失败
            $event->charge->order->setFailure();
        }
    }
}