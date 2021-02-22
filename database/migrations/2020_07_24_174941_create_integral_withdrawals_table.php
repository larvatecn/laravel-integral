<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegralWithdrawalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integral_withdrawals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('integral')->default(0);
            $table->unsignedInteger('amount')->default(0);
            $table->string('status', 10)->default('created');
            $table->string('channel', 30);
            $table->string('recipient');
            $table->text('metadata')->nullable();
            $table->timestamps();
            $table->timestamp('canceled_at', 0)->nullable();//成功时间
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
        Schema::dropIfExists('integral_withdrawals');
    }
}
