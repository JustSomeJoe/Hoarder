<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Download;
use App\Libraries\FileDownload;

class FileDownloadProcess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:downloads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download some files.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $total = Download::where('done', 0)->count();

        echo "Going to download {$total} files\n";

        $count=0;
        do {
            $fileDownload = new FileDownload;
            $fileDownload->saveFile(
                Download::getOneDownload()
            );
            $count++;
        }while($count <= $total);

    }
}
