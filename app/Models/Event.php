<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    // use HasFactory;
    //對應資料表
    protected $table = 'events';
    public $timestamps = false;

    //允許批量賦予
    protected $fillable = [
        'eventid',//
        'number',//
        'event',//
        'gametime',//
        'away_team',//
        'home_team',//
        'negative_odds',//
        'winning_odds',//
        'data_Sources',//
    ];

    //取得的刊登人
    // public function user(){
    //     return $this->belongsTo('app\User');
    // }


    //建立多對多關聯
    // public function teams()
    // {
    //     return $this->belongsToMany(Team::class)->withTimestamos();
    // }
}
