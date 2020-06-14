<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLabels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('labels', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('title')->nullable();
            $table->string('button1')->nullable();
            $table->string('button2')->nullable();
            $table->string('cause1')->nullable();
            $table->string('cause2')->nullable();
            $table->string('cause3')->nullable();
            $table->string('cause4')->nullable();
            $table->string('cause5')->nullable();
            $table->integer('last_update_by')->nullable()->unsigned();
            $table->timestamps();
            $table->foreign('last_update_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('labels');
    }
}
