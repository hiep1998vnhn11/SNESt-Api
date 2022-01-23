<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30)->comment('post, comment, like, follow, message, etc');
            $table->string('object_type', 30)->comment('post, comment, like, follow, message, etc');
            $table->unsignedBigInteger('object_id')->nullable();
            $table->string('object_url', 155)->nullable();
            $table->string('title', 155);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('seen_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
