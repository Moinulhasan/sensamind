<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateButtons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buttons', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->unsignedInteger('user_group');
            $table->unsignedTinyInteger('evolution');
            $table->string('button_label')->nullable();
            $table->string('cause1')->nullable();
            $table->string('cause2')->nullable();
            $table->string('cause3')->nullable();
            $table->string('cause4')->nullable();
            $table->string('cause5')->nullable();
            $table->timestamps();
        });
        Schema::table('user_clicks', function (Blueprint $table) {
            $table->foreign('button_id')->references('id')->on('buttons')->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buttons');
    }
}
