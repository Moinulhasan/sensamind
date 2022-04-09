<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTmpBluetoothClicks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tmp_bluetooth_clicks', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->unsignedInteger('user_group');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('evolution')->nullable();
            $table->unsignedInteger('button_id')->nullable();
            $table->string('button');
            $table->timestamp('clicked_at')->index('clicked_time');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('button_id')->references('id')->on('buttons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tmp_bluetooth_clicks');
    }
}
