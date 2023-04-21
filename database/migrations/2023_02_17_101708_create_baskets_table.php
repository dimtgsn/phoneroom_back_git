<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('baskets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('basket_product', function (Blueprint $table) {
//            $table->id();
            $table->unsignedBigInteger('quantity')->default(1);
            $table->unsignedBigInteger('basket_id');
            $table->unsignedBigInteger('product_id');
            $table->index('product_id');
            $table->index('basket_id');
            $table->timestamps();

            $table->foreign('basket_id')
                ->references('id')
                ->on('baskets')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->primary(['basket_id', 'product_id']);
//            $table->foreign('product_id')
//                ->references('id')
//                ->on('products')
//                ->cascadeOnUpdate()
//                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if(app()->isLocal()) {
            Schema::dropIfExists('basket_product');
            Schema::dropIfExists('baskets');
        }
    }
};
