<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Queue;
use App\Subreddit;

class SubList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sub:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List subreddits you are watching.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $subs = Subreddit::all();

        foreach($subs as $temp) {
            echo "{$temp->name}\n";
        }

        return;
    }
}
