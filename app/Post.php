<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

use App\Author;
use App\Subreddit;

class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * Do not use this and $guarded at the same time.
     *
     * @var array
     */
    protected $fillable = [
    	'id',
    	'post_id',
    	'domain',
    	'title',
    	'self_text',
    	'url',
    	'over_18',
    	'subreddit_id',
    	'author_id',
        'created_utc',
    ];

	public function setAuthorIdAttribute($value)
	{
		$author = Author::firstOrCreate(array('name' => $value));
		$this->attributes['author_id'] = $author->id;
	}

	public function setSubredditIdAttribute($value)
	{
		$author = Subreddit::firstOrCreate(array('name' => $value));
		$this->attributes['subreddit_id'] = $author->id;
	}

	public function setOver18Attribute($value)
	{
		$this->attributes['over_18'] = (int) $value;
	}

	public function scopeNew($query)
	{
		$query->where('done', '=', 0)
		->orderBy('id', 'ASC');

		return $query;
	}

	public function scopeCleanup($query)
	{
		$query->where('done', 2)
			->where('created_utc', '<', Carbon::now()->subHours(2))
			->limit(1000)
			->delete();
	}

	public function author()
	{
		return $this->belongsTo('App\Author');
	}

	public function subreddit()
	{
		return $this->belongsTo('App\Subreddit');
	}



}
