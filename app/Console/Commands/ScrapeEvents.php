<?php

namespace App\Console\Commands;

use App\Services\EventScraperService;
use Illuminate\Console\Command;
use App\Http\Controllers\EventScraperController;


class ScrapeEvents extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //命令
    protected $signature = 'event:scrape';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape events and store data in the database';

    // protected $scraperService;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {   //調用構建函數
        parent::__construct();
    }

    /**    protected $description = 'Scrape events and store data in the database';

     * Execute the console command.
     *
     * @return int
     */
    //Artisan 命令來觸發數據的自動爬取和存儲流程
    public function handle(
        EventScraperService $scraperService
    ) {   //解析EventScraperService
        // $scraperService = app(EventScraperService::class);
        //調用獲取scrap
        $scraperService->fetchEvent();
        $this->info('Events scraped and stored successfully.');
        //成功時返回0
        return 0;
    }

}