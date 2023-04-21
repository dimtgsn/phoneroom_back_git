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
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropColumn('product_id');
        });

        Schema::create('favorite_product', function (Blueprint $table) {
//            $table->id();
//            $table->unsignedBigInteger('quantity')->default(1);
            $table->unsignedBigInteger('favorite_id');
            $table->unsignedBigInteger('product_id');
            $table->index('product_id');
            $table->index('favorite_id');
            $table->timestamps();

            $table->foreign('favorite_id')
                ->references('id')
                ->on('favorites')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->primary(['favorite_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable();
            $table->index('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
        Schema::dropIfExists('favorite_product');
    }
};
