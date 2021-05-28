<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('gender', ['male', 'female'])->default('male');
            $table->text('profile_background_path')->default(config('app.url') . '/storage' . '/user/default-user-cocer-photo.jpeg');
            $table->date('birthday')->nullable();
            $table->string('live_at')->nullable();
            $table->string('from')->nullable();
            $table->text('link_to_social')->nullable();
            $table->string('story')->nullable();
            $table->enum('story_privacy', ['public', 'friend', 'private'])->default('public');
            $table->string('locale')->default('en');
            $table->boolean('show_live_at')->default(1);
            $table->boolean('show_from')->default(1);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('infos');
    }
}
