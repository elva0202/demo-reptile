<?php
namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class EventScraperService
{
    protected $url;
    //構造函數實例化會自動執行
    public function __construct()
    {   //將目標URL 賦值給$url
        $this->url = 'https://cp.zgzcw.com/lottery/jcplayvsForJsp.action?lotteryId=26&issue=2024-09-26';
    }

    public function fetchEvent()
    {
        //使用Http Client取代cURL 使用get請求
        $response = Http::get($this->url);

        //檢查請求是否成功
        if ($response->successful()) {
            //請求成功返回HTML內容
            $htmlContent = $response->body();
            //返回的html內容傳給processHtmlContent，進一步解析
            $this->processHtmlContent($htmlContent);
        } else {
            throw new \Exception('Failed to fetch data from ' . $this->url . ' with status code: ' . $response->status());
        }
    }
    //請求成功將返回到processHtmlContent內容進行解析
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

                //$gametime有值進行格式化，沒有返回null
                $formattedGametime = $gametime ? $gametime->format('Y-m-d H:i:s') : null;
                $formattedNegativeOdds = number_format((float) $negative_odds, 2);
                $formattedWinningOdds = number_format((float) $winning_odds, 2);

                // 儲存數據到資料庫中，如果已存在則更新，否則創建新紀錄
                Event::updateOrCreate(
                    //查尋條件
                    ['eventid' => $eventid],
                    //更新內容
                    [
                        'eventid' => $eventid,
                        'number' => $number,
                        'event' => $event,
                        'gametime' => $formattedGametime,
                        'away_team' => $away_team,
                        'home_team' => $home_team,
                        'negative_odds' => $negative_odds,
                        'winning_odds' => $winning_odds,
                        'data_Sources' => $data_Sources,
                    ]

                );
            }
        }
    }

    public function getFilteredMatches(Request $request)
    {
        // 從請求中獲取篩選條件
        $minOdds = $request->input('minOdds', 0);
        $teamkeyword = $request->input('teamkeyword', '');
        $minimumthreshold = $request->input('minimumthreshold', 0);
        \Log::info('接收到的隊名關鍵字:', ['teamkeyword' => $teamkeyword]);

        // 查詢資料庫，篩選符合條件的比賽數據
        $query = Event::query();

        // 清理並篩選隊伍名稱
        $teamkeyword = trim($teamkeyword);
        if (!empty($teamkeyword)) {
            $query->where(function ($q) use ($teamkeyword) {
                $q->where('away_team', 'like', "%$teamkeyword%")
                    ->orWhere('home_team', 'like', "%$teamkeyword%");
            });
        }

        // 賠率篩選 (需要確保 OR 和 AND 條件優先級正確)
        if ($minOdds > 0) {
            $query->where(function ($q) use ($minOdds) {
                $q->where('negative_odds', '>=', $minOdds)
                    ->orWhere('winning_odds', '>=', $minOdds);
            });
        }

        // 閥值篩選 (需要確保 OR 和 AND 條件優先級正確)
        if ($minimumthreshold > 0) {
            $query->where(function ($q) use ($minimumthreshold) {
                $q->where('negative_odds', '>=', $minimumthreshold)
                    ->orWhere('winning_odds', '>=', $minimumthreshold);
            });
        }



        // 獲取篩選結果
        return $query->get();


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
                if (isset($matches[1][0])) {
                    $odds = $this->cleanData($matches[1][0]);
                    //格式化為小數點後兩位
                    return number_format((float) $odds, 2);
                }
            }
        }
        return 'N/A';
    }

    protected function extractWinningOdds($cells)
    {
        if (isset($cells[6])) {
            if (preg_match_all('/<a\b[^>]*>(.*?)<\/a>/is', $cells[6]->ownerDocument->saveHTML($cells[6]), $matches)) {
                if (isset($matches[1][1])) {
                    $odds = $this->cleanData($matches[1][1]);
                    //格式化為小數點後
                    return number_format((float) $odds, 2);
                }
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
?>