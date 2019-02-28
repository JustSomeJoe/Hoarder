<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Queue;
use App\Subreddit;

class SubDelete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sub:delete {sub}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a subreddit from the subreddits table.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$sub = $this->argument('sub');

        if(!Subreddit::getByName($sub)->first()) {
            echo "You are not watching {$sub}, so I have nothing to delete.";
            return;
        }

        $result = Subreddit::where('name', $sub)->first();

        $result->delete();

        echo "{$sub} deleted from watch list";

        return;
    }
}
