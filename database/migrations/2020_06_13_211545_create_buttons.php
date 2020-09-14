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
            $table->string('button_label');
            $table->string('cause1');
            $table->string('cause2');
            $table->string('cause3');
            $table->string('cause4');
            $table->string('cause5');
            $table->string('node',10);
            $table->string('branch1',10)->nullable();
            $table->string('branch2',10)->nullable();
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
