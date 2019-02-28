<?php

namespace App\Libraries;

use App\Download;

class GfyCat extends Hoarder
{
	private $apiEndPoint = 'https://api.gfycat.com/v1/gfycats/%s';
	protected $job;
	private $gfyId;
	private $fileLink;

	public function run($job)
	{
		$this->job = $job;

		if(!$this->gfyId = self::getId()) {
			return false;
		}

		$post = $this->job->post;
		$subreddit = $this->job->post->subreddit;
		$author = $this->job->post->author;

		if(self::getFileOptions()) {

			if(!Download::byName($this->fileLink->url)->first()) {

				Download::create([

					'name' => $this->fileLink->url,
					'domain' => $post->domain,
					'subreddit_id' => $subreddit->id,
					'author_id' => $author->id,
					'post' => $post->id,
					'url' => $this->fileLink->url,
					'created_utc' => $post->created_utc,
					'type' => $this->fileLink->url,
					'size' => $this->fileLink->size,

				]);

				$this->closeJob('Done', "Grabbed GFY Video " .$post->id);

			}else{
				$this->closeJob('Failed','Duplicate file');
			}

		}

	}

	public function getFileOptions()
	{
		self::getRequest(self::getEndPoint());

		if(self::getCurlResponseCode() != 200) {
			$this->closeJob("Failed", "Bad link");
			return false;
		}

		$response = json_decode(self::getCurlResponse());

		if(!empty($response->gfyItem)) {

			if(empty($response->gfyItem->content_urls)) {
				return false;
			}

			$options = $response->gfyItem->content_urls;

			if(!empty($options->mobile)) {
				$this->fileLink = $options->mobile;
				return true;
			}

			if(!empty($options->mp4)) {
				$this->fileLink = $options->mp4;
				return true;
			}

			if(!empty($options->max2mbGif)) {
				$this->fileLink = $options->max2mbGif;
				$this->fileLink->size = empty($options->size) ? null : $options->size;
				return true;
			}

		}


		$this->closeJob('Failed', 'No desired file');

		return false;
	}

	public function getId()
	{
		preg_match('/.*gfycat.com\/([a-zA-Z]+)/', $this->job->url, $match);

		if(empty($match[1])) {
			$this->closeJob('Failed', 'No ID found');
			return false;
		}

		return trim($match[1]);
	}


	public function getEndPoint()
	{
		return sprintf($this->apiEndPoint, $this->gfyId);
	}

}
