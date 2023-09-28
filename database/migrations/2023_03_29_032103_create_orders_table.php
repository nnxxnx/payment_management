<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('payment_account_id')->unsigned()->comment('支付账号ID');
            $table->string('serial_number')->comment('流水号');
            $table->string('user_name')->comment('账户姓名');
            $table->string('mobile')->comment('手机');
            $table->string('id_number')->comment('身份证号');
            $table->string('bank_account')->comment('银行卡号');
            $table->decimal('amount')->comment('付款金额');
            $table->text('note')->comment('备注')->nullable();
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->timestamp('pre_processed_at')->comment('就绪时间')->nullable();
            $table->timestamp('pay_at')->comment('付款时间')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('payment_account_id',)
                ->references('id')
                ->on('payment_accounts')
                ->onUpdate('cascade')
                ->onDelete('no action');
            $table->index('serial_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
