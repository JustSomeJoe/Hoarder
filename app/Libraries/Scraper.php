<?php

namespace App\Libraries;

use App\Timer;
use App\Post;
use App\Subreddit;
use Illuminate\Support\Carbon;


class	Scraper extends Hoarder {

	public $rawBody;
	private $pageLimit = 10;

	public function fetch($area='postsNew', $options=[])
	{
		if(!self::timer()) {
			die("To Fast");
		}

		$this->rawBody = [];

		$this->uri = $this->endpoints($area, $options);

		$this->debug("Requesting: " . $this->uri);

		try {

			$this->getRequest($this->uri);

			$this->rawBody = json_decode(self::getCurlResponse());

			if(self::getCurlResponseCode() != 200) {

				$this->debug("Got a " . self::getCurlResponseCode() . ' response.');
				$this->debug("Moving on!");

				if(strstr($area, 'posts')) {

					$subreddit = Subreddit::getByName($options[0])->first();

					$subreddit->status = self::getCurlResponseCode();
					$subreddit->last_checked = Carbon::now();
					$subreddit->save();

				}
				return false;
			}

		} catch (Exception $e) {
			//
		}

		return $this;
	}

	public function postsCatchUp($subreddit, $before='')
	{
		$go = true;

		while($go) {

			if(!self::fetch('postsNewer', [$subreddit, self::setRedditId($before)])) {
				$go = false;
				continue;
			}

			$this->debug("Looking for posts added after ID " . $before);

			$this->debug("Found: " . self::getPostsCount() . " posts");

			foreach (self::getPosts() as $postData) {

				$post = (array) self::singlePostFormat($postData);

				if(!Post::find($post['id'])) {
					Post::create(
						$post
					);
				}
			}

			$before = self::getBefore();

			if(self::getPostsCount() < 100) {
				$go = false;
			}

		}

	}

	public function postsGetFullArchive($subreddit='')
	{
		$this->debug('Starting: ' . $subreddit);
		$this->debug("Page limit: " . self::getPageLimit());


		$after = '';

		$go = true;

		$pageCounter = 1;

		while($go) {

			if(!self::fetch('postsOlder', [$subreddit, self::setRedditId($after)])) {
				$go = false;
				continue;
			}

			$this->debug("Page: " . $pageCounter);
			$this->debug("Found " . count(self::getPosts()) . ' posts');

			foreach (self::getPosts() as $postData) {

				$post = (array) self::singlePostFormat($postData);

				if(!Post::find($post['id'])) {
					Post::create(
						$post
					);
				}
			}

			$after = self::getAfter();

			if(self::getPostsCount() < 100 || $pageCounter >= self::getPageLimit()) {
				$go = false;

				$this->debug("Done here, time to move on");
			}

			$pageCounter++;
		}
	}

	public function getFirstChild()
	{
		return self::getPosts()[0];
	}

	public function getRawBody()
	{
		return $this->rawBody;
	}

	public function getPosts()
	{
		if(empty(self::getRawBody()->data->children)) {
			return [];
		}

		return $this->getRawBody()->data->children;
	}

	public function getPostsCount()
	{
		return count(self::getPosts());
	}

	public function singlePostFormat($data)
	{
		if(empty($data->data)) {
			return false;
		}

		$out = new \stdClass();
		$out->id = self::baseTo10($data->data->id);
		$out->subreddit_id = $data->data->subreddit;
		$out->author_id = $data->data->author;
		$out->domain = $data->data->domain;
		$out->url = $data->data->url;
		$out->created_utc = Carbon::createFromTimestamp($data->data->created_utc);
		$out->over_18 = $data->data->over_18;
		$out->post_id = $data->data->id;
		$out->title = $data->data->title;
		$out->self_text = $data->data->title;

		return $out;

	}

	public function getBefore()
	{
		return $this->getRawBody()->data->before;
	}

	public function getAfter()
	{
		return $this->getRawBody()->data->after;
	}

	public function timer()
	{
		$timer  = new Timer;
		$timer->save();

		$count = Timer::hits()->count();

		switch($count) {
			case 30:
			usleep(5000000);
			$this->debug("# Took a five second nap");
			break;
			case 29:
			usleep(3000000);
			$this->debug("# Took a three second nap");
			break;
			case 27:
			usleep(2000000);
			$this->debug("# Took a two second nap");
			break;
			case 25:
			usleep(1000000);
			$this->debug("# Took a one second nap");
			break;
		}

		return ($count > 30) ? false : true;
	}

	public function endpoints($type='subreddit', $options)
	{
		$endpoints =  [
			'subreddit' => 'https://www.reddit.com/r/%s/about.json',
			'postsOlder' => 'https://www.reddit.com/r/%s/new/.json?sort=new&after=t3_%s&limit=100',
			'postsNewer' => 'https://www.reddit.com/r/%s/new/.json?sort=new&before=t3_%s&limit=100',
			'user' => 'https://www.reddit.com/user/%s/submitted/.json?sort=new&before=%s&limit=100',
		];

		return vsprintf($endpoints[$type], $options);
	}

	public static function baseTo10($str='')
	{
		if(empty($str)) return;
		return base_convert($str, 36, 10);
	}

	public static function baseTo36($int='')
	{
		if(empty($int)) return;
		return base_convert($int, 10, 36);
	}

	public function setRedditId($id = '')
	{
		if(strstr($id, '_')) {
			list($a, $b) = explode('_', $id);
			$id = $b;
		}
		return $id;
	}

	public function getPageLimit()
	{
		if(empty($this->pageLimit) || $this->pageLimit > 10) {
			$this->pageLimit = 10;
		}

		return $this->pageLimit;
	}

	public function setPageLimit($int=10)
	{
		$this->pageLimit = (int) $int;
		return $this;
	}

	public function getUri()
	{
		return $this->uri;
	}


}
