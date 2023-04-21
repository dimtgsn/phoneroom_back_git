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
        Schema::create('options', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id')->unique();
            $table->index('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->jsonb('options_json');

            $table->timestamps();
        });

        DB::statement('CREATE INDEX "options_options_json_idx" ON "options" USING GIN (options_json)');

        Schema::create('category_option', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('category_id');
            $table->index('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('option_id');
            $table->index('option_id');
            $table->foreign('option_id')
                ->references('id')
                ->on('options')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

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
        if (app()->isLocal()){
            Schema::dropIfExists('category_option');
            Schema::dropIfExists('options');
        }
    }
};
