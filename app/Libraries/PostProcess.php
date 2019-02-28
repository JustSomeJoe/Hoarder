<?php

namespace App\Libraries;

use App\External;
use App\Post;
use App\Download;
use App\Dupe;
use App\Subreddit;

class PostProcess extends Hoarder {

	private $badHosts = '';

	private $limit = 1000;

	public function fetch()
	{
		return Post::new()->limit($this->limit)->get();
	}

	public function process()
	{
		$go = true;

		$this->debug("Starting Process");

		while($go) {

			$this->records = self::fetch();

			$this->debug("Fetched " . count($this->records) . " records");

			$ids = [];
			foreach($this->records as $temp) {
				$ids[] = $temp->id;
			}

			Post::whereIn('id', $ids)->update(['done' => 1]);

			self::rawImages();
			self::imgurNonDirectImages();
			self::imgurAlbums();
			self::gfycat();
			self::eroshare();
			self::vidble();
			self::erome();

			if($this->records->count() < $this->limit) {
				$go = false;
			}

			echo "Ran ".$this->records->count()."\n";

		}

	}

	public function erome()
	{
		$this->debug("Running Erome");

		$count = 0;
		foreach($this->records as $post) {

			if($post->domain == 'erome.com') {

				Subreddit::updatePost($post);

				$post->done = 2;
				$post->save();

				self::insertExternal('erome', $post->url, $post->id);

				$count++;
			}

		}

		$this->debug("Found {$count}");
	}

	public function vidble()
	{
		$this->debug("Running Vidble");

		$count = 0;
		foreach($this->records as $post) {

			// Images
			if(preg_match('/https:\/\/.*vidble.com\/show\/([0-9a-zA-Z]+)/', $post->url, $match)) {

				Subreddit::updatePost($post);

				$post->done = 2;
				$post->save();

				$post->url = 'https://vidble.com/'.$match[1].'.jpg';
				self::insertDownload($post);
				$count++;
			}

			// Videos
			if(preg_match('/https:\/\/.*vidble.com\/watch\?v=([0-9a-zA-Z]+)/', $post->url, $match)) {

				Subreddit::updatePost($post);

				$post->done = 2;
				$post->save();

				$post->url = 'https://vidble.com/'.$match[1].'.mp4';
				self::insertDownload($post);
				$count++;
			}

		}

		$this->debug("Found {$count}");
	}

	public function eroshare()
	{
		$this->debug("Running Eroshare");

		$count = 0;
		foreach($this->records as $post) {

			if($post->domain == 'eroshare.com') {

				Subreddit::updatePost($post);

				$post->done = 2;
				$post->save();

				self::insertExternal('eroshare', $post->url, $post->id);

				$count++;
			}

		}

		$this->debug("Found {$count}");
	}

	public function gfycat()
	{
		$this->debug("Running GfyCat");

		$count = 0;
		foreach($this->records as $post) {

			if($post->domain == 'gfycat.com') {

				Subreddit::updatePost($post);

				$post->done = 2;
				$post->save();

				self::insertExternal('gfycat', $post->url, $post->id);

				$count++;
			}

		}

		$this->debug("Found {$count}");
	}

	public function imgurAlbums()
	{
		$this->debug("Running Imgur albums");

		$count = 0;
		foreach($this->records as $post) {

			if (preg_match_all(
				"/https?:\/\/w?w?w?a?i?m?.?imgur\.com\/[a|gallery]+\/(\w{4,11})/i",
				$post->url . ' ' . $post->selftext, $matches
			)) {

				if(!empty($matches[0])) {

					Subreddit::updatePost($post);

					$post->done = 2;
					$post->save();

					foreach($matches[0] as $album) {

						self::insertExternal('imgur', $album, $post->id);

					}

					$count++;
				}

			}

		}

		$this->debug("Found {$count}");
	}

	public function imgurNonDirectImages()
	{
		$this->debug("Running Imgur Non Direct Images");

		$count = 0;
		foreach($this->records as $post) {

			preg_match_all(
				"/https?:\/\/[i|m]?.?imgur\.com\/(\w{4,11})/i"
				, $post->url . " \n " . $post->self_text, $matches
			);

			if(!empty($matches[1])) {

				Subreddit::updatePost($post);

				$post->done = 2;
				$post->save();

				foreach($matches[1] as $image) {

					$post->url = 'https://i.imgur.com/'.$image.'.jpg';
					self::insertDownload($post);

					$count++;
				}

			}
		}
		$this->debug("Found {$count}");
	}

	public function rawImages()
	{
		$this->debug("Running Raw Images");

		$count = 0;
		foreach($this->records as $post) {

			preg_match_all(
				"/https?:\/\/{$this->badHosts}[\w.\/-]+(\.jpe?g|\.png|\.gif)/i"
				, $post->url . " \n " . $post->self_text, $matches
			);

			if(!empty($matches[0])) {

				Subreddit::updatePost($post);

				$post->done = 2;
				$post->save();

				foreach($matches[0] as $image) {

					$post->url = $image;
					self::insertDownload($post);

					$count++;
				}

			}
		}

		$this->debug("Found {$count}");
	}

	public function insertDownload($post, $type='image/jpg')
	{
		try {

			if(!$existing = Download::byName($post->url)->first()) {

				Download::create([

					'name' => $post->url,
					'domain' => $post->domain,
					'subreddit_id' => $post->subreddit_id,
					'author_id' => $post->author_id,
					'post' => $post->id,
					'url' => $post->url,
					'created_utc' => $post->created_utc,
					'type' => $post->url

				]);

			}else{

				Dupe::create([
					'parent' => $existing->id,
					'name' => $post->url,
					'subreddit_id' => $post->subreddit_id,
					'author_id' => $post->author_id,
					'post' => $post->id,
					'created_utc' => $post->created_utc,
				]);

			}

		} catch (Exception $e) {
			//
		}

	}

	public function insertExternal($area, $url, $post)
	{
		if(External::ByAreaAndUrl($area, $url)->first())
		{
			return false;
		}

		try {
			External::create([
				'url' => $url,
				'area' => $area,
				'post_id' => $post,
			]);
		} catch (Exception $e) {
				//
		}
	}

}
