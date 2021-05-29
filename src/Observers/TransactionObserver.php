<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

declare (strict_types=1);

namespace Larva\Integral\Observers;

use Larva\Integral\Exceptions\IntegralException;
use Larva\Integral\Models\IntegralWallet;
use Larva\Integral\Models\Transaction;

/**
 * 积分交易观察者
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class TransactionObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param Transaction $transaction
     * @return void
     * @throws IntegralException
     */
    public function created(Transaction $transaction)
    {
        //开始事务
        $dbConnection = IntegralWallet::onWriteConnection()->getConnection();
        $dbConnection->beginTransaction();
        try {
            $wallet = IntegralWallet::query()->where('user_id', '=', $transaction->user_id)->lockForUpdate()->first();
            $wallet->update(['integral' => $transaction->current_integral]);//更新用户积分
            $dbConnection->commit();
        } catch (\Exception $e) {//回滚事务
            $dbConnection->rollback();
            throw new IntegralException($e->getMessage(), 500);
        }
    }
}