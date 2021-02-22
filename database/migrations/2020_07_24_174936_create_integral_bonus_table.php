<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntegralBonusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integral_bonus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('paid');//是否已经赠送
            $table->unsignedBigInteger('user_id');
            $table->unsignedInteger('integral');
            $table->nullableMorphs('source');//关联对象
            $table->string('description', 60)->nullable();
            $table->string('transaction_id')->nullable();//关联的交易流水ID
            $table->text('metadata')->nullable();//metadata 参数 数组，一些源数据。
            $table->timestamp('created_at', 0)->nullable();//创建时间
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integral_bonus');
    }
}
