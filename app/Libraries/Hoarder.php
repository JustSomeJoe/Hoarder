<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Storage;

use GuzzleHttp\Client;

if (!function_exists('debug'))
{
	function debug($s, $die=false)
	{
		print_r($s);
		($die) ? $die : false;
	}
}

/**
 *
 */
class Hoarder
{
	protected $debug = true;
	protected $rawBody;
	protected $curlResponseCode;
	public $image;
	public $saveLocation;

	public function getSavedFileLocation()
	{
		return Storage::disk('local')
		->getAdapter()
		->getPathPrefix() .
		self::getSaveLocation();
	}

	public function isWritable()
	{
		$path = Storage::disk('local')
		->getAdapter()
		->getPathPrefix();

		if(!is_writable($path)) {
			throw new Exception("Error: {$path} is not writable", 1);
		}
	}


	public function setSaveLocation($saveLocation='')
	{
		$this->saveLocation = $saveLocation;

		$this->debug("Save location: " . self::getSaveLocation());

		return $this;
	}

	public function getSaveLocation()
	{
		return $this->saveLocation;
	}

	// Build download path
	public function buildDownloadPath($image)
	{
		$this->image = $image;

		return strtoupper($this->image->author->name[0]) . '/' .
		$this->image->author->name . '/' .
		$this->image->name .
		'.' . pathinfo($this->image->url)['extension'];
	}

	public function debug($s)
	{
		if($this->debug === false)
			return;

		echo "{$s}\n";
	}

	public function setRequestHeaders($array=[])
	{
		$this->requestHeaders = $array;
		return $this;
	}

	public function getRequestHeaders()
	{
		$default = ['User-Agent' => self::userAgent()];

		if(!empty($this->requestHeaders)) {
			$default = array_merge($default, $this->requestHeaders);
		}

		return $default;
	}

	public function getRequest($link='')
	{
		$this->client = new \GuzzleHttp\Client();

		$this->debug("Get request: " . $link);

		try {
			$response = $this->client->request('GET', $link, [
				'headers' => self::getRequestHeaders(),
				'http_errors' => false
			]);
		} catch (Exception $e) {
			return false;
		}

		self::setCurlResponseCode($response->getStatusCode());

		if(self::getCurlResponseCode() != 200) {
			return false;
		}

		$this->setCurlResponse($response->getBody());
	}

	public function setCurlResponse($body='')
	{
		$this->rawBody = $body;
	}

	public function getCurlResponse()
	{
		return $this->rawBody;
	}

	public function getCurlResponseCode()
	{
		return $this->curlResponseCode;
	}

	public function setCurlResponseCode($code)
	{
		$this->curlResponseCode = (int) $code;
	}

	public function setDebug($bool)
	{
		$this->debug = (bool) $bool;
	}

	public function closeJob($code,$notes=null)
	{
		$this->job->done = $code;
		$this->job->notes = $notes;
		$this->job->save();

		$this->debug($notes);
	}


	public function userAgent()
	{
		$agents = [
			'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727; .NET CLR 1.1.4322)',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.2; WOW64; Trident/7.0; tb-gmx/2.6.6; MAARJS; rv:11.0) like Gecko',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_2) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0.2 Safari/605.1.15',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36 OPR/54.0.2952.51',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:65.0) Gecko/20100101 Firefox/65.0',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/18.17763',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
			'Mozilla/4.0 (compatible; MSIE 5.5; Windows 98)',
			'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
			'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36'

		];

		return $agents[array_rand($agents)];
	}

}
