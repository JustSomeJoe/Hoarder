<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libraries\PostProcess;


class PostProcessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process new posts for new downloads and externals.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $process = new PostProcess();
        $process->process();
    }
}
