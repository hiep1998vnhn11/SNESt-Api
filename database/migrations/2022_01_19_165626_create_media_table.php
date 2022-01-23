<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('url');
            $table->string('type', 25);
            $table->float('size', 20, 2);
            $table->string('extension', 15);
            $table->string('mime_type', 40);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('thumbnail_id')->nullable();
            $table->unsignedBigInteger('object_id')->nullable();
            $table->string('object_type', 50)->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('thumbnail_id')->references('id')->on('media')->onDelete('cascade');
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
        Schema::dropIfExists('media');
    }
}
