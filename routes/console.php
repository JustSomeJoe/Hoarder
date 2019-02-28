<?php

use App\Queue;
use App\Download;
use App\Post;
use App\Subreddit;
use App\Timer;
use App\External;

use Illuminate\Foundation\Inspiring;
use App\Libraries\Scraper;
use App\Libraries\FileDownload;
use App\Libraries\PostProcess;
use App\Libraries\Imgur;
use App\Libraries\GfyCat;
use App\Libraries\Hoarder;
use Illuminate\Support\Carbon;


/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('test', function() {

})->describe('Clean up timer entries to keep table small.');


Artisan::command('postsCleanup', function () {

	Post::cleanup();

})->describe('Clean up older posts that are marked as having been processed.');

Artisan::command('timerCleanup', function() {

	Timer::cleanup();

})->describe('Clean up timer entries to keep table small.');
