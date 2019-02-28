<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dupe extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * Do not use this and $guarded at the same time.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'parent',
        'subreddit_id',
        'author_id',
        'post',
        'created_utc',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] =  substr(md5($value), 0, 10);
    }

}
