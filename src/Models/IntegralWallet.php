<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

declare (strict_types=1);

namespace Larva\Integral\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * 积分钱包
 * @property int $user_id
 * @property int $integral 可用积分
 * @property Carbon|null $created_at 创建时间
 * @property Carbon|null $updated_at 更新时间
 *
 * @property \Illuminate\Foundation\Auth\User $user
 * @property Recharge[] $recharges 充值记录
 * @property Transaction[] $transactions 交易记录
 * @property Withdrawals[] $withdrawals 提现记录
 */
class IntegralWallet extends Model
{
    /**
     * @var string 主键字段名
     */
    protected $primaryKey = 'user_id';

    /**
     * @var bool 关闭自增
     */
    public $incrementing = false;

    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'integral_wallets';

    /**
     * 该模型是否被自动维护时间戳.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 可以批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'integral'
    ];

    /**
     * 模型的默认属性值。
     *
     * @var array
     */
    protected $attributes = [
        'integral' => 0,
    ];

    /**
     * 为数组 / JSON 序列化准备日期。
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * Get the user that the charge belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.' . config('auth.guards.web.provider') . '.model'));
    }

    /**
     * 积分赠送明细
     * @return HasMany
     */
    public function bonus(): HasMany
    {
        return $this->hasMany(Bonus::class, 'user_id', 'user_id');
    }

    /**
     * 赠送积分
     * @param int $integral
     * @param string $description
     * @return Model|Bonus
     */
    public function give(int $integral, string $description)
    {
        return $this->bonus()->create(['integral' => $integral, 'description' => $description]);
    }

    /**
     * 充值明细
     * @return hasMany
     */
    public function recharges(): HasMany
    {
        return $this->hasMany(Recharge::class, 'user_id', 'user_id');
    }

    /**
     * 交易明细
     * @return hasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id', 'user_id');
    }

    /**
     * 提现明细
     * @return hasMany
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawals::class, 'user_id', 'user_id');
    }

    /**
     * 创建充值请求
     * @param string $channel 渠道
     * @param int $amount 金额 单位分
     * @param string $type 支付类型
     * @param string|null $clientIP 客户端IP
     * @return Model|Recharge
     */
    public function rechargeAction(string $channel, int $amount, string $type, string $clientIP = null)
    {
        return $this->recharges()->create(['channel' => $channel, 'amount' => $amount, 'type' => $type, 'client_ip' => $clientIP]);
    }

    /**
     * 积分提现
     * @param int $integral 提现的积分数量
     * @param string $channel 提现渠道
     * @param string $recipient 收款账户
     * @param array $metaData 附加信息
     * @return false|Model|Withdrawals
     */
    public function withdrawalsAction(int $integral, string $channel, string $recipient, array $metaData = [])
    {
        $currentIntegral = $this->integral + $integral;
        if ($currentIntegral < 0) {//计算后如果余额小于0，那么结果不合法。
            return false;
        }
        return $this->withdrawals()->create([
            'integral' => $integral,
            'channel' => $channel,
            'status' => Withdrawals::STATUS_PENDING,
            'recipient' => $recipient,
            'metadata' => $metaData
        ]);
    }

    /**
     * 提现到微信
     * @param int $integral
     * @param string $recipient
     * @param array $metaData
     * @return false|Withdrawals
     */
    public function withdrawalByWechat(int $integral, string $recipient, array $metaData = [])
    {
        return $this->withdrawalsAction($integral, \Larva\Transaction\Transaction::CHANNEL_WECHAT, $recipient, $metaData);
    }

    /**
     * 提现到支付宝账户
     * @param int $integral
     * @param string $account 支付宝账号
     * @param array $metaData
     * @return false|Withdrawals
     */
    public function withdrawalByAlipay(int $integral, string $account, array $metaData = [])
    {
        return $this->withdrawalsAction($integral, \Larva\Transaction\Transaction::CHANNEL_ALIPAY, $account, $metaData);
    }
}
