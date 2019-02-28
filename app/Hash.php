<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Hash extends Model
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
    	'hash',
    	'download_id'
    ];

    public function download()
    {
    	return $this->belongsTo('App\Download');
    }

    public function scopeByHash($query, $hash='')
    {
    	$query->where('hash', $hash);

    	return $query;
    }

}
