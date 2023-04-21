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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->jsonb('variants')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('old_price')->nullable();
            $table->unsignedBigInteger('price')->nullable();
            $table->unsignedBigInteger('units_in_stock')->default(0);
            $table->unsignedDecimal('rating', $precision = 2, $scale = 1)->default(0.0);

            $table->unsignedBigInteger('category_id');
            $table->index('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('tag_id')->nullable();
            $table->index('tag_id');
            $table->foreign('tag_id')
                ->references('id')
                ->on('tags')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->unsignedBigInteger('brand_id');
            $table->index('brand_id');
            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->timestamps();

            DB::statement('CREATE INDEX "products_variants_idx" ON "products" USING GIN (variants)');
        });

        Schema::create('product_tag', function (Blueprint $table) {
            $table->primary(['tag_id', 'product_id']);

            $table->unsignedBigInteger('tag_id');
            $table->index('tag_id');
            $table->foreign('tag_id')
                ->references('id')
                ->on('tags')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('product_id');
            $table->index('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (app()->isLocal()) {
            Schema::dropIfExists('product_tag');
            Schema::dropIfExists('products');
        }
    }
};
