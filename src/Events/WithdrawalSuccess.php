<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Integral\Events;

use Illuminate\Queue\SerializesModels;
use Larva\Integral\Models\Withdrawal;

/**
 * 提现成功事件
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class WithdrawalSuccess
{
    use SerializesModels;

    /**
     * @var Withdrawal
     */
    public $withdrawal;

    /**
     * RefundFailure constructor.
     * @param Withdrawal $withdrawal
     */
    public function __construct(Withdrawal $withdrawal)
    {
        $this->withdrawal = $withdrawal;
    }
}