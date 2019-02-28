<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Queue;
use App\Subreddit;

class SubAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sub:add {sub}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new subreddit to subreddits table.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$sub = $this->argument('sub');

        if(Subreddit::getByName($sub)->first()) {
            echo "You are already watching {$sub}.";
            return;
        }

        Subreddit::create([
            'name' => $sub
        ]);

        echo "{$sub} added to watch list";

        return;
    }
}
