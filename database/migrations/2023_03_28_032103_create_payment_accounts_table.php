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
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_name')->comment('账户姓名');
            $table->string('mobile')->comment('手机');
            $table->string('id_number')->comment('身份证号');
            $table->string('bank_account')->comment('银行卡号');
            $table->string('ada_pay_member_id')->comment('AdaPay MemberId');
            $table->string('ada_pay_account_id')->comment('AdaPay AccountId');
            $table->text('note')->comment('备注')->nullable();
            $table->tinyInteger('status')->default(1)->comment('状态');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_accounts');
    }
};
