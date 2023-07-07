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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('comment');
            $table->string('advantages')->nullable();
            $table->string('disadvantages')->nullable();
            // type 0(default) - regular writer. And type 1 - admin/manager
            $table->unsignedTinyInteger('type')->default(0);
            $table->unsignedTinyInteger('rating')->default(0);
            $table->unsignedBigInteger('product_id');
            $table->index('product_id');

            $table->unique(['product_id', 'user_id']);

            $table->unsignedBigInteger('user_id');
            $table->index('user_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unsignedBigInteger('answer_id')->nullable();
            $table->foreign('answer_id')
                ->references('id')
                ->on('comments')
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
        Schema::dropIfExists('comments');
    }
};
