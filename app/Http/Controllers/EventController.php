<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\EventScraperService;


class EventController extends Controller
{
    protected $scraperService;

    public function __construct(EventScraperService $scraperService)
    {
        $this->scraperService = $scraperService;
    }

    //顯示視圖
    public function showPage()
    {
        return view('event');  // 返回 resources/views/event.blade.php 視圖
    }
    public function loadMatches(Request $request)
    {
        //請求數據進行驗證
        $request->validate([
            //賠率為依序進行驗證，必填，數值，區間範圍0~99'bail', 
            'minOdds' => ['bail', 'required', 'numeric', 'min:0', 'max:99'],
            //隊伍可為空，字串，簡體中文'nullable'
            'teamkeyword' => ['nullable', 'string'],
        ]);
        // 獲取篩選結果
        $matches = $this->scraperService->getFilteredMatches($request);
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
        //從event中獲得所有數據整合成一個陣列
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
