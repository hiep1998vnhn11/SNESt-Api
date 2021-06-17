<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('thresh_id');
            $table->text('media')->nullable();
            $table->string('media_type', 10)->nullable();
            $table->string('media_name', 255)->nullable();
            $table->text('content')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('thresh_id')->references('id')->on('threshes')->onDelete('cascade');
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
        Schema::dropIfExists('messages');
    }
}
