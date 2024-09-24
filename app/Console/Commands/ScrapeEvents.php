<?php

namespace App\Console\Commands;

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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    //Artisan 命令來觸發數據的自動爬取和存儲流程
    public function handle()
    {
        $scraper = new EventScraperController();
        $scraper->fetchEvent();//

        $this->info('Events scraped and stored successfully.');

    }

}
