<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Post;
use Illuminate\Support\Carbon;

class Subreddit extends Model
{
	public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * Do not use this and $guarded at the same time.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'status',
        'last_id',
        'last_checked'
    ];


    public function scopeFetchOneToScrape($query)
    {
        $query->where('status', 200);
        $query->orderBy('last_checked', 'ASC');

        return $query;
    }

    public function scopeGetByName($query, $name='')
    {
        $query->where('name', $name);
        return $query;
    }

    public function scopeFetchUnredToScrape($query)
    {
        $query->where('status', 200);
        $query->whereNull('last_checked');
        $query->orderBy('last_id', 'DESC');

        return $query;
    }

    public function scopeFetchTimedToScrape($query)
    {
        $sub = $query->where('hour_delay', ">", 0)
            ->whereRaw('last_checked < DATE_SUB(NOW(), INTERVAL hour_delay MINUTE)');
        return $query;
    }

    public function scopeFetchOlderToScrape($query)
    {
        $sub = $query->where('hour_delay', "=", 0)
            ->orderBy('last_checked', 'ASC');
        return $query;
    }

    public function scopeUpdatePost($query, $post)
    {
        $sub = Subreddit::where('id', $post->subreddit_id)->first();

        if((int) $sub->last_id < (int) $post->id) {
            $sub->last_id = $post->id;
        }

        $sub->save();
    }

    public function posts()
    {
        return $this->hasMany('App\Post');
    }

}
