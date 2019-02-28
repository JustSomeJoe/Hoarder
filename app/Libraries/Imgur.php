<?php

namespace App\Libraries;

use App\Download;

/**
 *
 */
class Imgur extends Hoarder
{
	protected $job;
	protected $curlResponseCode;
	private $albumName;
	protected $clientId = '1d8d9b36339e0e2'; // Stolen from Reddit Enhancement Suite

	public function run($job)
	{
		$this->job = $job;

		if(!strstr($this->job->url, '/a/') &&
			!strstr($this->job->url, '/gallery/')) {

			$this->closeJob('Failed', 'Not an album');
			return;

		}

		if(!$this->albumName = self::extractAlbumNameFromURL()) {

			$this->closeJob('Failed', 'Cannot find album');
			return;

		}

		if(!$images = self::getAlbumImages($this->albumName)) {

			$this->closeJob('Failed', 'Album empty');

		};

		if(!$message = self::addImagesToDownload($images)) {

			return false;

		}

		$this->closeJob('Done', $message);

	}

	public function addImagesToDownload($images)
	{
		if(empty($images) || !empty($images->error)) {

			if(!empty($images->error)) {
				$this->closeJob('Failed', $images->error);
				return false;
			}

			$this->closeJob('Failed', 'No images with status code: ' . $this->curlResponseCode);

			return false;
		}

		$post = $this->job->post;
		$subreddit = $this->job->post->subreddit;
		$author = $this->job->post->author;

		$found = count($images);
		$saved = 0;
		foreach($images as $image) {

			if(!Download::byName($image->link)->first()) {

				Download::create([

					'name' => $image->link,
					'domain' => $post->domain,
					'subreddit_id' => $subreddit->id,
					'author_id' => $author->id,
					'post' => $post->id,
					'url' => $image->link,
					'created_utc' => $post->created_utc,
					'album' => $this->albumName,
					'type' => $image->type,
					'size' => $image->size,

				]);
				$saved++;
			}
		}

		return "Album images saved {$saved} out of {$found}";

	}

	public function extractAlbumNameFromURL()
	{
		preg_match("/https?:\/\/w?w?w?a?i?m?.?imgur\.com\/[a|gallery]+\/(\w{4,11})/i",
			$this->job->url,
			$match
		);

		if(empty($match[1])) {
			return false;
		}

		return $match[1];
	}

	public function getAlbumImages($albumName='')
	{
		$uri = 'https://api.imgur.com/3/album/' . $albumName . '/images';

		self::setRequestHeaders(['Authorization' => "Client-ID {$this->clientId}"]);

		self::getRequest($uri);

		$out = json_decode(self::getCurlResponse());

		if (empty($out->data))
		{
			return false;
		}

		return $out->data;

	}




}
