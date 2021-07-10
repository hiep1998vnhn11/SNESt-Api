<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 155);
            $table->string('last_name', 100)->nullable();
            $table->string('full_name', 255);
            $table->string('slug', 255);
            $table->string('email')->unique()->nullable();
            $table->string('phone_number')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->text('profile_photo_path')->nullable();
            // $table->text('profile_photo_path')->default(config('app.url') . '/storage' . '/user/default-user-avatar.jpeg');
            $table->string('url');
            $table->string('provider_oauth')->nullable();
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
        Schema::dropIfExists('users');
    }
}
