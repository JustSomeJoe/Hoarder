<?php

namespace App\Libraries;

use App\Download;
use voku\helper\HtmlDomParser;

class Erome extends Hoarder
{
	protected $job;

	public function run($job)
	{
		$this->job = $job;
		$videoSource = null;

		if(!self::getReqest($this->job->url)) {

			$html = HtmlDomParser::str_get_html(self::getCurlRespose());

			if(!empty($html->find('div.video-lg')[0])) {

				if(!empty($html->find('div.video-lg')[0]->find('source')[0])) {
					$videoSource = $html->find('div.video-lg')[0]
						->find('source')[0]
						->getAttribute('src');
				}

			}

			if(empty($videoSource)) {

				$this->closeJob('Failed', 'Unable to get video link.');
				return;

			}

			if(!Download::byName($videoSource)->first()) {

				$post = $this->job->post;
				$subreddit = $this->job->post->subreddit;
				$author = $this->job->post->author;

				Download::create([

					'name' => $videoSource,
					'domain' => $post->domain,
					'subreddit_id' => $subreddit->id,
					'author_id' => $author->id,
					'post' => $post->id,
					'url' => $videoSource,
					'created_utc' => $post->created_utc,
					'type' => $videoSource,

				]);

				$this->closeJob('Done');

			}else{
				$this->closeJob('Failed','Duplicate file');
			}

		}
	}
}
