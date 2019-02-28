<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Post;

class External extends Model
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
    	'done',
    	'area',
    	'url',
    	'post_id',
        'notes',
    ];

    public function scopeGetJob($query)
    {
        if(!$job = $query->where('done', 'New')->orderBy('id', 'ASC')->first()) {
            return false;
        }

        // $job->done = 'Working';
        // $job->save();

        return $job;
    }

    public function scopeByAreaAndUrl($query, $area='', $url='')
    {
        $query->where('area', $area);
        $query->where('url', $url);
        return $query;
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

}
