<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('zipcode');
            $table->tinyInteger('age')->unsigned();
            $table->tinyInteger('gender')->unsigned();
            $table->integer('current_evolution')->unsigned()->default(1)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('lock_out_code')->nullable();
            $table->boolean('failed_logins')->default(0);
            $table->string('role')->default('user');
            $table->rememberToken();
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
