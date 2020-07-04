<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEvolutions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evolutions', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('title');
            $table->string('description');
            $table->integer('button_1')->unsigned();
            $table->integer('button_2')->unsigned();
            $table->timestamps();
            $table->foreign('button_1')->references('id')->on('labels')->onDelete('cascade');
            $table->foreign('button_2')->references('id')->on('labels')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('current_evolution')->references('id')->on('evolutions')->onDelete('set null');
        });

        Schema::table('user_clicks', function (Blueprint $table){
            $table->foreign('evolution')->references('id')->on('evolutions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evolutions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_current_evolution_foreign');
        });

        Schema::table('user_clicks', function (Blueprint $table) {
            $table->dropForeign('user_clicks_evolution_foreign');
        });
    }
}
