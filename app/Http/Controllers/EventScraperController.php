<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use App\Services\EventScraperService;

class EventScraperController extends Controller
{
    protected $scraperService;
    //構造函數，依賴注入EventScraperService
    //每次創建EventScraperService自動注入服務
    public function __construct(EventScraperService $scraperService)
    {
        $this->scraperService = $scraperService;
    }

    //用服務器抓取比賽數據
    public function fetchEvent()
    {
        //調用scraperService
        $this->scraperService->fetchEvent();
        //返回json格式
        return response()->json(['message' => 'data fetched and processed successfully']);
    }
}
