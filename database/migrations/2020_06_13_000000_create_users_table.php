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
            $table->unsignedInteger('user_group')->default(1)->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('zipcode');
            $table->unsignedTinyInteger('age');
            $table->unsignedTinyInteger('gender');
            $table->string('argued',25);
            $table->unsignedTinyInteger('current_evolution')->default(1)->nullable();
            $table->unsignedInteger('current_btn1')->default(1)->nullable();
            $table->unsignedInteger('current_btn2')->default(1)->nullable();
            $table->string('evolution_path')->nullable();
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
