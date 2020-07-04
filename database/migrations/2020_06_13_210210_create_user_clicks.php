<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserClicks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_clicks', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->integer('evolution')->unsigned()->nullable();
            $table->string('button');
            $table->string('cause');
            $table->timestamp('clicked_at')->index('clicked_time');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_clicks');
    }
}