<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Http;

class EventScraperController extends Controller
{
    //拿取網頁上的比賽數據
    public function fetchEvent()
    {
        //爬取目標網站
        $url = "https://cp.zgzcw.com/lottery/jcplayvsForJsp.action?lotteryId=26&issue=2024-09-22";

        //使用Http Client取代cURL 使用get請求
        $response = Http::get($url);

        //檢查請求是否成功
        if ($response->successful()) {
            //請求成功返回HTML內容
            $htmlContent = $response->body();

            //返回的html內容傳給processHtmlContent，進一步解析
            $this->processHtmlContent($htmlContent);
            //返回json格式
            return response()->json(['message' => 'data fetched and processed successfully']);
        } else {
            //錯誤則返回錯誤訊息
            return response()->json(['erroe' => 'Failed to fetch data'], 500);
        }
    }
    //請求成功將返回內容processHtmlContent進行解析
    protected function processHtmlContent($htmlContent)
    {
        //用DOMDocument解析html
        $dom = new \DOMDocument();
        // 使用 @ 符號來避免 HTML 格式錯誤時顯示警告
        @$dom->loadHTML($htmlContent);
        //根據getElementsByTagName名稱獲取所有匹配元素（獲取所有table元素）
        $tables = $dom->getElementsByTagName('table');

        // if ($tables->length > 0) {
        //     echo "成功找到表格數據！";
        // } else {
        //     echo "未能找到表格數據！";
        // }

        //遍歷table標籤
        foreach ($tables as $table) {
            //從table中提取tbody
            $tbodys = $table->getElementsByTagName('tbody');
            //遍歷每個tbody
            foreach ($tbodys as $tbody) {
                //將tbody傳給paresTbody近一步解析
                $this->parseTbody($tbody);
            }
        }
    }

    //解析<tbody>比賽數據
    protected function parseTbody($tbody)
    {   //遍歷tbody中的tr標籤
        foreach ($tbody->getElementsByTagName('tr') as $row) {
            //獲取所有td元素
            $cells = $row->getElementsByTagName('td');
            //提取比賽數據
            if ($cells->length > 1) {
                $eventid = $this->extractEventId($row);
                $number = $this->extractNumber($cells);
                $event = $this->extractEvent($cells);
                $gametime = $this->extractGameTime($row);
                $away_team = $this->extractAwayTeam($cells);
                $home_team = $this->extractHomeTeam($cells);
                $negative_odds = $this->extractNegativeOdds($cells);
                $winning_odds = $this->extractWinningOdds($cells);
                $data_Sources = $this->extractDataSources($cells);

                // 在此處進行調試輸出，確認提取到的數據是否正確
                // dump([
                //     'eventid' => $eventid,
                //     'number' => $number,
                //     'event' => $event,
                //     'gametime' => $gametime,
                //     'away_team' => $away_team,
                //     'home_team' => $home_team,
                //     'negative_odds' => $negative_odds,
                //     'winning_odds' => $winning_odds,
                //     'data_Sources' => $data_Sources,
                // ]);

                //$gametime有值進行格式化，沒有返回null
                $formattedGametime = $gametime ? $gametime->format('Y-m-d H:i:s') : null;
                // 儲存數據到資料庫中，如果已存在則更新，否則創建新紀錄
                Event::updateOrCreate(
                    ['eventid' => $eventid],
                    [
                        'eventid' => $eventid,
                        'number' => $number,
                        'event' => $event,
                        'gametime' => $gametime,
                        'away_team' => $away_team,
                        'home_team' => $home_team,
                        'negative_odds' => $negative_odds,
                        'winning_odds' => $winning_odds,
                        'data_Sources' => $data_Sources
                    ]

                );
            }
        }
    }

    protected function extractEventId($row)
    {
        if (preg_match('/<tr\b[^>]*\bid=["\']?([0-9a-zA-Z_]+)["\']?/i', $row->ownerDocument->saveHTML($row), $matches)) {
            return preg_replace('/\D/', '', $this->cleanData($matches[1]));
        }
        return null;
    }

    protected function extractNumber($cells)
    {
        if (isset($cells[0])) {
            if (preg_match('/<i\b[^>]*>(.*?)<\/i>/is', $cells[0]->ownerDocument->saveHTML($cells[0]), $matches)) {
                return $this->cleanData($matches[1]);
            }
        }
        return 'N/A';
    }

    protected function extractEvent($cells)
    {
        return isset($cells[1]) ? $this->cleanData(strip_tags($cells[1]->textContent)) : 'N/A';
    }

    protected function extractGameTime($row)
    {
        if (preg_match('/<span\b[^>]*title=["\']?比赛时间:([0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2})["\']?/i', $row->ownerDocument->saveHTML($row), $matches)) {
            return \DateTime::createFromFormat('Y-m-d H:i', $matches[1]) ?: null;
        }
        return null;
    }

    protected function extractAwayTeam($cells)
    {
        return isset($cells[3]) ? preg_replace('/\[.*?\]/', '', $this->cleanData(strip_tags($cells[3]->textContent))) : 'N/A';
    }

    protected function extractHomeTeam($cells)
    {
        return isset($cells[5]) ? preg_replace('/\[.*?\]/', '', $this->cleanData(strip_tags($cells[5]->textContent))) : 'N/A';
    }

    protected function extractNegativeOdds($cells)
    {
        if (isset($cells[6])) {
            if (preg_match_all('/<a\b[^>]*>(.*?)<\/a>/is', $cells[6]->ownerDocument->saveHTML($cells[6]), $matches)) {
                return isset($matches[1][0]) ? $this->cleanData($matches[1][0]) : 'N/A';

            }
        }
        return 'N/A';
    }

    protected function extractWinningOdds($cells)
    {
        if (isset($cells[6])) {
            if (preg_match_all('/<a\b[^>]*>(.*?)<\/a>/is', $cells[6]->ownerDocument->saveHTML($cells[6]), $matches)) {
                return isset($matches[1][1]) ? $this->cleanData($matches[1][1]) : 'N/A';
            }
        }
        return 'N/A';
    }

    protected function extractDataSources($cells)
    {
        return isset($cells[7]) ? $this->cleanData(strip_tags($cells[7]->textContent)) : 'N/A';
    }

    protected function cleanData($data)
    {
        return trim(preg_replace('/\s+/', ' ', $data));
    }
}
