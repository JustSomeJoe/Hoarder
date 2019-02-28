<?php

namespace App;

use App\Author;
use App\Subreddit;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Download extends Model
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
        'domain',
        'hash',
        'album',
        'subreddit_id',
        'author_id',
        'post',
        'type',
        'size',
        'url',
        'created_utc',
    ];

    public function setNameAttribute($value)
    {
        $this->attributes['name'] =  substr(md5($value), 0, 10);
    }

    public function setTypeAttribute($value)
    {
        if(!strstr($value, '.')) {
            $this->attributes['type'] =  $value;
            return;
        }

        $ext = pathinfo($value, PATHINFO_EXTENSION);
        $type = 'unknown';

        switch($ext) {
            case 'jpg':
            case 'jpeg':
            $type = 'image/jpg';
            break;
            case 'gif':
            $type = 'image/gif';
            break;
            case 'mp4':
            $type = 'video/mp4';
            break;
            case 'png':
            $type = 'image/png';
            break;
        }

        $this->attributes['type'] =  $type;
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function subreddit()
    {
        return $this->belongsTo(Subreddit::class);
    }

    public function scopeByName($query, $name='')
    {
        $query->where('name', substr(md5($name), 0, 10));
        return $query;
    }

    public function scopeGetQueue($query, $limit=10)
    {
        $query->where('done', 0)->with('author')->limit($limit);
        return $query;
    }

    public static function getOneDownload()
    {
        $lock = substr(md5(uniqid(mt_rand())), 0, 8);

        DB::update(
            DB::raw(
                "UPDATE downloads SET lock_hash = '{$lock}' WHERE lock_hash IS NULL LIMIT 1;"
            )
        );

        return Download::where('lock_hash', $lock)->first();

    }

}
