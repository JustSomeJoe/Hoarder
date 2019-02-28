<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Queue;
use App\Subreddit;
use App\Libraries\Scraper;
use Illuminate\Support\Carbon;

class SubProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:subs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Full crawl of new subreddits.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		if($subreddits = Subreddit::fetchOneToScrape()->get()) {

			foreach($subreddits as $subreddit) {

				$subreddit->last_checked = Carbon::now();
				$subreddit->save();

				$scrape = new Scraper();
				$scrape->setPageLimit(10)
				->postsGetFullArchive(
					$subreddit->name
				);

                sleep(1);
			}
		}
    }
}
