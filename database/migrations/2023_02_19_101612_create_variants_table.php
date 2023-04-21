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
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->index('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->jsonb('variants_json');
            $table->timestamps();
        });

        DB::statement('CREATE INDEX "variants_variants_json_idx" ON "variants" USING GIN (variants_json)');

        Schema::create('category_variant', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('category_id');
            $table->index('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('variant_id');
            $table->index('variant_id');
            $table->foreign('variant_id')
                ->references('id')
                ->on('variants')
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
        Schema::dropIfExists('category_variant');
        Schema::dropIfExists('variants');
    }
};
