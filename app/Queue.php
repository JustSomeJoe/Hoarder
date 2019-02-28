<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
	public $timestamps = false;

	protected $fillable = [
		'name',
		'order',
	];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('order', 'DESC');
        });

    }



}
