<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Timer extends Model
{

	public function scopeHits($query)
	{
		$query->where('created_at', '>',
			Carbon::now()->subMinutes(
				1
			)
		);

		return $query;
	}

	public function scopeCleanup($query, $minutes=10)
	{
		$query->where('created_at', '<',
			Carbon::now()->subMinutes(
				(int) $minutes
			)
		)->delete();
	}

}
