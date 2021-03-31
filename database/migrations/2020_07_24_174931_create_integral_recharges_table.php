<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegralRechargesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integral_recharges', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->string('channel', 10);
            $table->string('type', 10);
            $table->unsignedInteger('amount')->default(0);
            $table->ipAddress('client_ip')->nullable();//发起支付请求客户端的 IP 地址
            $table->string('status', 10)->default('pending');
            $table->timestamps();
            $table->timestamp('succeeded_at', 0)->nullable();//成功时间
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integral_recharges');
    }
}
