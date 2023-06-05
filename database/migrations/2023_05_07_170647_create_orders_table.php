<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('status_id')->nullable();
            $table->index('status_id');

            $table->foreign('status_id')
                ->references('id')
                ->on('order_statuses')
                ->cascadeOnUpdate()
                ->nullOnDelete();


            $table->text('ship_address');
            $table->text('description')->nullable();

            $table->unsignedBigInteger("total");
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
