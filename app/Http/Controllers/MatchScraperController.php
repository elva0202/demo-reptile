<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades;
use App\Models\Event;

class MatchScraperController extends Controller
{
    //
    public function filter(Request $request)
    {
        //設定預設值 
        //從id編號1開始，返回筆數
        $marker = isset($request->marker) ? $request->marker : 1;
        $limit = isset($request->limit) ? $request->limit : 10;

        //獲取篩選條件
        $teamkeyword = isset($request->team) ? $request->team : null;
        $minodds = isset($request->odds) ? $request->odds : null;

        //初始化
        $query = Event::query();

        //隊伍篩選 返回away_team或home_team有包含的關鍵字數據
        if ($teamkeyword) {
            $query->where(function ($q) use ($teamkeyword) {
                $q->where('away_team', 'like', "%$teamkeyword%")
                    ->orWhere('home_team', 'like', "%$teamkeyword%");
            });
        }

        // 賠率篩選 返回大於等於查詢數值的數據
        if ($minodds) {
            $query->where(function ($q) use ($minodds) {
                $q->where('negative_odds', '>=', $minodds)
                    ->orWhere('winning_odds', '>=', $minodds);
            });
        }
        //獲取結果(支持分頁)返回10筆
        $events = $query->paginate(10);
        return response()->json($events);

    }
}