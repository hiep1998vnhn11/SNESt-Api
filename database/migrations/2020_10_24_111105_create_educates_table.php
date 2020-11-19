<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEducatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('educates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('info_id');
            $table->string('school')->nullable();
            $table->string('details');
            $table->boolean('status')->default(1);
            $table->foreign('info_id')->references('id')->on('infos')->onDelete('cascade');
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
        Schema::dropIfExists('educates');
    }
}
