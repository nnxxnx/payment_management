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
        Schema::create('order_process_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned()->comment('订单ID');
            $table->text('note')->nullable()->comment('备注');
            $table->timestamps();

            $table->foreign('order_id',)
                ->references('id')
                ->on('orders')
                ->onUpdate('cascade')
                ->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_process_logs');
    }
};
