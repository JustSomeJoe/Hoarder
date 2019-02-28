<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Storage;
use App\Hash;
use App\Dupe;
use \Gumlet\ImageResize;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

Class FileDownload extends Hoarder {

	private $originalPath;
	private $curlConnection;
	private $curlResponseBody;
	protected $curlResponseCode;

	private $errorMessage;

	private $downloadPath;


	public function saveFile($image)
	{
		if(empty($image)) {
			return false;
		}

		$path = parent::buildDownloadPath($image);

		self::save(
			$path
		);
	}

	public function save($saveLocation='')
	{
		if(empty($saveLocation)) {
			return false;
		}

		if(!parent::setSaveLocation($saveLocation)) {
			return false;
		}

		self::downloadFile();

		$this->debug("Done with download");

		if(in_array(self::getCurlResponseCode(), [200,301])) {
			$this->writeFile();
			$this->debug("Wrote the file to disk");
		}else{
			$this->debug("\nSkipped Write. Response code: " . $this->getCurlResponseCode() . "\n");
		}

		$this->image->done = $this->getCurlResponseCode();
		$this->image->save();

		return true;
	}

	public function writeFile()
	{
		$this->debug("Writing to: " . self::getSaveLocation());

		if(file_exists(self::getSaveLocation())) {
			$this->debug("\nSkipping: file already exists\n");
			return true;
		}

		Storage::disk('local')->put(
			self::getSaveLocation(),
			$this->curlResponseBody
		);

		if(filesize(self::getSavedFileLocation()) < 10) {

			$this->debug("\nThe file was bad. Deleting record and file######\n");

			$this->image->delete();

			unlink(self::getSavedFileLocation());

			return true;
		}

		// Log the md5 hash of the file and check for duplicates
		if(self::storeMd5()) {
			// Create the thumbnail
			self::createThumbnail();
		}

		self::storeHex();

		return true;
	}

	public function createThumbnail()
	{
		$this->debug("Starting Thumbnail Run");

		$this->filePath = parent::getSavedFileLocation();

		$this->debug("Path: " . $this->filePath);

		$this->fileExtension = pathinfo($this->filePath, PATHINFO_EXTENSION);
		$this->fileName = pathinfo($this->filePath, PATHINFO_FILENAME);
		$this->dirName = pathinfo($this->filePath, PATHINFO_DIRNAME);

		$this->pathThumb = $this->dirName . '/' . $this->fileName . '_s.jpg';

		if($this->fileExtension == 'mp4') {
			self::createVideoThumbnail();
		}else{
			self::createImageThumbnail();
		}

	}

	public function createVideoThumbnail()
	{
		$this->debug("Starting Video Thumbnail");

		try {

			$ffmpeg = \FFMpeg\FFMpeg::create();

			$video = $ffmpeg->open(
				$this->filePath
			);

			$i=8;
			do {
				$video->frame(
					\FFMpeg\Coordinate\TimeCode::fromSeconds($i)
				)
				->save(
					$this->pathThumb
				);

				if(file_exists($this->pathThumb)) {

					$this->debug("Video thumb created!");

					try {
						$image = new ImageResize($this->pathThumb);
						$image->crop(90, 90, ImageResize::CROPCENTER);
						$image->save($this->pathThumb);
					} catch (Exception $e) {
						$this->debug("\n################## Final thumb failed to create: " . $this->pathThumb . "\n");
						return false;
					}

					break;
				}
				$this->debug("\nNo thumb at this time code, going back one second.\n");

				$i--;

			}while($i>0);

			if(!file_exists($this->pathThumb)) {

				$this->debug("\n################## Final thumb failed to create: " . $this->pathThumb . "\n");
			}


		} catch (Exception $e) {
			$this->debug("Error: " . $e->getMessage());
		}

		$this->debug("Finished with {$this->pathThumb}");

	}

	public function createImageThumbnail()
	{
		$this->debug("Starting Image Thumbnail Run");
		$this->debug("Path: " . $this->filePath);

		if(!in_array($this->fileExtension, ['jpg','gif','png'])) {
			return false;
		}

		try {
			$image = new ImageResize($this->filePath);
			$image->crop(90, 90, ImageResize::CROPCENTER);
			$image->save($this->pathThumb);
		} catch (Exception $e) {
			$this->debug("\n################## Final thumb failed to create: " . $this->pathThumb . "\n");
			return false;
		}

		if(!file_exists($this->pathThumb)) {
			$this->debug("\n################## Final thumb failed to create: " . $this->pathThumb . "\n");
		}

		$this->debug("Created: " . $this->pathThumb);
	}

	/**
	 * Store hex for hamming distance comparison
	 */
	public function storeHex()
	{
		if(empty($this->image->id)) {
			return false;
		}

		if($this->image->type == 'image/jpeg' || $this->image->type == 'image/jpg') {

			$hasher = new ImageHash(new DifferenceHash());

			$filePath = self::getSavedFileLocation();

			try {

				$hash = $hasher->hash($filePath);
				$hex = $hash->toHex();

			} catch (Exception $e) {
				$this->debug("\nBad File " . $filePath . "\n");
				return;
			}

			if($hashRecord = Hash::where('download_id', $this->image->id)->first()) {
				$hashRecord->hex = $hex;
				$hashRecord->save();
			}

		}

		return;

	}

	/**
	*
	* Capture md5 hash information for file and then check
	* for duplicates. If one is found, log the dupe.
	*/
	public function storeMd5()
	{
		$md5 = md5_file(
			self::getSavedFileLocation()
		);

		if(!$existing = Hash::byHash($md5)->first()) {
			Hash::create([
				'hash' => $md5,
				'download_id' => $this->image->id
			]);
			return true;
		}

		Dupe::create([
			'parent' => $existing->download_id,
			'name' => $this->image->url,
			'subreddit_id' => $this->image->subreddit_id,
			'author_id' => $this->image->author_id,
			'post' => $this->image->id,
			'created_utc' => $this->image->created_utc,
		]);

		return false;
	}

	/**
	*
	* Download file from source
	*/
	public function downloadFile()
	{
		$this->debug("Beginning curl download");

		$this->debug("Original Location: " . self::getOriginalPath());

		self::getRequest(self::getOriginalPath());

		try {
			$this->curlResponseBody = self::getCurlResponse();
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		$this->curlResponseCode = self::getCurlResponseCode();

		$this->debug("Response " . self::getCurlResponseCode());

		return true;
	}

	public function setError($string)
	{
		$this->errorMessage = $string;
		return $this;
	}

	public function getError()
	{
		return (self::hasError()) ? self::getError() : false;
	}

	public function hasError()
	{
		return empty($this->errorMessage) ? false : true;
	}

	public function getOriginalFileName()
	{
		return basename(self::getOriginalPath());
	}

	public function getOriginalPath()
	{
		return $this->image->url;
	}

}
