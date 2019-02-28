<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
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
    ];

    public function images()
    {
        return $this->hasMany('App\Download');
    }

}
