<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\view\view;

class EventController extends Controller
{
    public function showPage()
    {
        return view('event');  // 返回 resources/views/event.blade.php 視圖
    }
    public function loadMatches(Request $request)
    {
        // 從請求中獲取篩選條件
        $minOdds = $request->input('minOdds', 0);
        $teamkeyword = $request->input('teamkeyword', '');
        $minimumthreshold = $request->input('minimumthreshold', 0);

        // 查詢資料庫，篩選符合條件的比賽數據
        $query = Event::query();

        if (!empty($teamkeyword)) {
            $query->where('away_team', 'like', "%$teamkeyword%")
                ->orWhere('home_team', 'like', "%$teamkeyword%");
        }

        if ($minOdds > 0) {
            $query->where('negative_odds', '>=', $minOdds)
                ->orWhere('winning_odds', '>=', $minOdds);
        }

        if ($minimumthreshold > 0) {
            $query->where('negative_odds', '>=', $minimumthreshold)
                ->orWhere('winning_odds', '>=', $minimumthreshold);
        }

        // 獲取篩選結果
        $matches = $query->get();

        // 返回 JSON 格式的篩選結果
        return response()->json($matches);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //處理請求顯示所有事件
    public function index(Request $request)
    {
        //獲取所有數據
        $events = Event::all();
        return response()->json($events);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function showEvents(): view
    {
        //從Event獲取數據傳到events.index進行顯示
        //將獲取到的數據整合返回一個陣列
        $events = Event::all();

        return view('events.index', compact('events'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        //
    }
}
