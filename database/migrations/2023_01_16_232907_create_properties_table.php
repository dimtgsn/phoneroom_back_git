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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id')->unique();
            $table->index('product_id');
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->jsonb('properties_json');
            $table->timestamps();
        });

        DB::statement('CREATE INDEX "properties_properties_json_idx" ON "properties" USING GIN (properties_json)');

        Schema::create('category_property', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('category_id');
            $table->index('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('property_id');
            $table->index('property_id');
            $table->foreign('property_id')
                ->references('id')
                ->on('properties')
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
            Schema::dropIfExists('category_property');
            Schema::dropIfExists('properties');
        }
    }
};
