<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('eventid');
            $table->unsignedInteger('number');
            $table->string('event');
            $table->dateTime('gametime');
            $table->string('away_team');
            $table->string('home_team');
            $table->float('negative_odds');
            $table->float('winning_odds');
            $table->string('data_Sources');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
