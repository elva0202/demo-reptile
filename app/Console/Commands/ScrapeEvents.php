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
    protected $signature = 'event:scrape';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape events and store data in the database';

    protected $scraperService;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(EventscraperService $scraperService)
    {
        parent::__construct();
        //注入
        $this->scraperService = $scraperService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    //Artisan 命令來觸發數據的自動爬取和存儲流程
    public function handle()
    {
        $this->scraperService->fetchEvent();
        $this->info('Events scraped and stored successfully.');
        //成功時返回0
        return 0;
    }

}