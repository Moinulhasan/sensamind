<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('user_group')->references('id')->on('user_groups')->onDelete('set default');
        });
        Schema::table('user_clicks', function (Blueprint $table) {
            $table->foreign('user_group')->references('id')->on('user_groups')->onDelete('set default');
        });
        Schema::table('buttons', function (Blueprint $table) {
            $table->foreign('user_group')->references('id')->on('user_groups')->onDelete('set default');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_groups');
    }
}
