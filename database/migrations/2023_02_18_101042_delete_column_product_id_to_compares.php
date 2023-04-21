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
            $table->dropColumn('product_id');
            $table->dropColumn('category_id');
//            $table->dropPrimary(array('product_id', 'category_id'));
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
            $table->unsignedBigInteger('product_id')->nullable();
            $table->index('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
