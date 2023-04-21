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
        Schema::table('compares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        Schema::create('compare_product', function (Blueprint $table) {
//            $table->id();
//            $table->unsignedBigInteger('quantity')->default(1);
            $table->unsignedBigInteger('compare_id');
            $table->unsignedBigInteger('product_id');
            $table->index('product_id');
            $table->index('compare_id');
            $table->timestamps();

            $table->foreign('compare_id')
                ->references('id')
                ->on('compares')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('category_id');
            $table->index('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->primary(['compare_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('compares', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        Schema::dropIfExists('compare_product');
    }
};
