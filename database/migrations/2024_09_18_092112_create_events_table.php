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

    //創建events資料表儲存比賽數據
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');        //自增主鍵ID
            $table->unsignedInteger('eventid'); //比賽ID整數
            $table->unsignedInteger('number');  //比賽編號整數
            $table->string('event');            //賽事
            $table->dateTime('gametime');       //比賽時間
            $table->string('away_team');        //客隊
            $table->string('home_team');        //主隊
            $table->float('negative_odds');     //負賠率 浮點數
            $table->float('winning_odds');      //勝賠率 浮點數
            $table->string('data_Sources');     //數據來源

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
