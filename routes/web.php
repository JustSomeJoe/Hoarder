<?php

use App\External;
use App\Download;
use App\Post;
use App\Author;
use App\Subreddit;
use App\Timer;
use App\Libraries\Scraper;
use App\Libraries\PostProcess;
use App\Libraries\FileDownload;

use Illuminate\Support\Carbon;
use Illuminate\Http\Request;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


if (!function_exists('debug'))
{
    function debug($s,$die=false)
    {
        echo "<pre>";
        print_r($s);
        echo "</pre>";
        ($die) ? die() : false;
    }
}

Route::get('/image/queue', function() {

    $images = Download::getQueue(10)->get();

    foreach($images as $image) {

        $image->done = 1;
        $image->save();
    }

    foreach ($images as $key => $value) {

        $file = new FileDownload;
        $file->make($value->url)->saveImage($value);

        $value->done = $file->getCurlResponseCode();
        $value->save();

    }

});


Route::get('/', function () {
    return view('welcome');
});

Route::get('/process', function () {

 $x = new PostProcess();

 return $x->process();

});

Route::get('/test', function () {


$s = Subreddit::where('id', '2300')
             ->with(['posts' => function($q) {
                 $q->orderBy('id', 'DESC')->take(1);
             }])
             ->get();

return $s;

});

Route::get('/posts/cleanup', function (Request $request) {
    Post::CleanUp();
});

Route::get('/posts/older', function (Request $request) {
    $scrape = new Scraper();
    $scrape->postsGetFullArchive('gonewild');
});

Route::get('/posts/newer', function (Request $request) {

    $post = Post::getLastPost('gonewild')->first();

        return $post->subreddit->name;

    $scrape = new Scraper();

    $scrape->postsCatchUp(
        $subreddit->name,
        Scraper::baseTo36(
            $subreddit->last_id
        )
    );


    die('Done');


    // $after = empty($_GET['after']) ? '' : $_GET['after'];

    // $after = '';

    // for($i=0;$i<=30; $i++) {

    //     $scrape->fetch('posts', ['gonewild', $after ]);

    //     foreach ($scrape->getPosts() as $postData) {
    //         try {
    //             Post::firstOrCreate(
    //                 (array) $scrape->singlePostFormat($postData)
    //             );
    //         } catch (Exception $e) {

    //         }

    //     }

    //     $after = $scrape->getAfter();

    //     if($scrape->getPostsCount() < 100) {
    //         die("Got to end");
    //     }

    // }


});
