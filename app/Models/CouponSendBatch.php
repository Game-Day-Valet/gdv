<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponSendBatch extends Model
{
	use HasFactory;

	protected $fillable = [
		'coupon_id',
		'initiated_by',
		'total_recipients',
		'sent_count',
		'failed_count',
		'status',
		'message',
		'started_at',
		'finished_at',
	];

	protected $casts = [
		'started_at' => 'datetime',
		'finished_at' => 'datetime',
	];

	public function coupon()
	{
		return $this->belongsTo(Coupon::class, 'coupon_id');
	}
}


