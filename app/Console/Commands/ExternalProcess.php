<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\External;
use App\Libraries\Imgur;
use App\Libraries\GfyCat;
use App\Libraries\Erome;
use Illuminate\Support\Carbon;

class ExternalProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:externals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process External Links.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $numberToRun = External::where('done', 'New')->count();

        echo "Found {$numberToRun} items to run\n";

    	$count=1;
    	do {

    		if($job = External::getJob()) {

    			if($job->area == 'imgur') {
    				$imgur = new Imgur;
    				$imgur->run($job);
                    sleep(1);
    			}

    			if($job->area == 'gfycat') {
    				$gfycat = new GfyCat;
    				$gfycat->run($job);
    			}

    			if($job->area == 'erome') {
    				$gfycat = new Erome;
    				$gfycat->run($job);
    			}
    		}

    		$count++;
    	}while($count <= $numberToRun);
    }
}
