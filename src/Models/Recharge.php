<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Integral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Larva\Integral\Events\RechargeFailure;
use Larva\Integral\Events\RechargeShipped;
use Larva\Integral\Notifications\RechargeSucceeded;
use Larva\Transaction\Models\Charge;

/**
 * 积分充值
 * @property int $id 流水号
 * @property int $user_id 用户ID
 * @property int $integral 积分数
 * @property int $amount 金额 单位分
 * @property string $channel 渠道
 * @property string $type 类别
 * @property string $status 状态
 * @property string $client_ip 客户端IP
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 * @property Carbon|null $succeeded_at 成功时间
 *
 * @property Charge $charge 关联付款单
 * @property User $user 关联用户
 * @property Transaction $transaction 关联交易
 * @property IntegralWallet $wallet 关联钱包
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Recharge extends Model
{

    const STATUS_PENDING = 'pending';//处理中： pending
    const STATUS_SUCCEEDED = 'succeeded';//完成： succeeded
    const STATUS_FAILED = 'failed';//失败： failed

    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'integral_recharges';

    /**
     * 可以批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'amount', 'integral', 'channel', 'type', 'status', 'client_ip', 'succeeded_at'
    ];

    /**
     * 应该被调整为日期的属性
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'succeeded_at'
    ];

    /**
     * 模型的默认属性值。
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    /**
     * 为数组 / JSON 序列化准备日期。
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * Get the user that the charge belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(
            config('auth.providers.' . config('auth.guards.web.provider') . '.model')
        );
    }

    /**
     * 关联钱包
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wallet()
    {
        return $this->belongsTo(IntegralWallet::class, 'user_id', 'user_id');
    }

    /**
     * 关联交易
     * Get the entity's transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function transaction()
    {
        return $this->morphOne(Transaction::class, 'source');
    }

    /**
     * 关联赠送
     * Get the entity's bonus.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function bonus()
    {
        return $this->morphOne(Bonus::class, 'source');
    }

    /**
     * 关联付款单
     * Get the entity's charge.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function charge()
    {
        return $this->morphOne(Charge::class, 'order');
    }

    /**
     * 设置交易成功
     */
    public function setSucceeded()
    {
        $this->update(['channel' => $this->charge->channel, 'type' => $this->charge->type, 'status' => static::STATUS_SUCCEEDED, 'succeeded_at' => $this->freshTimestamp()]);

        $this->transaction()->create([
            'user_id' => $this->user_id,
            'type' => Transaction::TYPE_RECHARGE,
            'description' => trans('integral.integral_recharge'),
            'integral' => $this->integral,
            'current_integral' => bcadd($this->wallet->integral, $this->integral)
        ]);

        if ($this->integral >= settings('integral.recharge_gift_mix', 100000)) {//赠送
            $gift = bcmul(settings('integral.recharge_gift', 0), $this->integral);
            if ($gift > 0) {
                $this->bonus()->create(['user_id' => $this->user_id, 'integral' => $gift, 'description' => trans('integral.recharge_gift')]);
            }
        }
        event(new RechargeShipped($this));
        $this->user->notify(new RechargeSucceeded($this->user, $this));
    }

    /**
     * 设置交易失败
     */
    public function setFailure()
    {
        $this->update(['status' => static::STATUS_FAILED]);
        event(new RechargeFailure($this));
    }

    /**
     * 状态
     * @return string[]
     */
    public static function getStatusLabels()
    {
        return [
            static::STATUS_PENDING => '等待付款',
            static::STATUS_SUCCEEDED => '充值成功',
            static::STATUS_FAILED => '充值失败',
        ];
    }

    /**
     * 获取状态Dot
     * @return string[]
     */
    public static function getStatusDots()
    {
        return [
            static::STATUS_PENDING => 'info',
            static::STATUS_SUCCEEDED => 'success',
            static::STATUS_FAILED => 'warning',
        ];
    }
}
