<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Integral;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Class IntegralServiceProvider
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class IntegralServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->publishes([
                __DIR__ . '/../resources/lang' => resource_path('lang'),
            ], 'integral-lang');
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'integral');

        // Transaction
        Event::listen(\Larva\Transaction\Events\ChargeClosed::class, \Larva\Integral\Listeners\ChargeClosedListener::class);//支付关闭
        Event::listen(\Larva\Transaction\Events\ChargeFailure::class, \Larva\Integral\Listeners\ChargeFailureListener::class);//支付失败
        Event::listen(\Larva\Transaction\Events\ChargeShipped::class, \Larva\Integral\Listeners\ChargeShippedListener::class);//支付成功
        Event::listen(\Larva\Transaction\Events\TransferFailure::class, \Larva\Integral\Listeners\TransferFailureListener::class);//提现失败
        Event::listen(\Larva\Transaction\Events\TransferShipped::class, \Larva\Integral\Listeners\TransferShippedListener::class);//提现成功

        // Observers
        \Larva\Integral\Models\Recharge::observe(\Larva\Integral\Observers\RechargeObserver::class);
        \Larva\Integral\Models\Transaction::observe(\Larva\Integral\Observers\TransactionObserver::class);
        \Larva\Integral\Models\Withdrawals::observe(\Larva\Integral\Observers\WithdrawalsObserver::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }

}