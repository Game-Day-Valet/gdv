<?php

namespace App\Jobs;

use App\Mail\CouponEmail;
use App\Models\CouponSendBatch;
use App\Models\User;
use App\Repositories\CouponRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCouponBatchJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public int $timeout = 1200; // 20 minutes

	public function __construct(public int $batchId)
	{
	}

	public function handle(CouponRepositoryInterface $couponRepository): void
	{
		$batch = CouponSendBatch::find($this->batchId);
		if (!$batch) {
			Log::warning('SendCouponBatchJob: batch not found', ['batch_id' => $this->batchId]);
			return;
		}

		try {
			$batch->update(['status' => 'processing', 'started_at' => now(), 'message' => null]);

			$coupon = $couponRepository->find($batch->coupon_id);
			if (!$coupon) {
				$batch->update(['status' => 'failed', 'message' => 'Coupon not found', 'finished_at' => now()]);
				return;
			}

			$users = User::whereNotNull('email_verified_at')
				->whereHas('roles', function ($q) { $q->where('name', 'user'); })
				->whereNotNull('email')
				->get(['id','email']);

			$total = $users->count();
			$sent = 0; $failed = 0;
			$batch->update(['total_recipients' => $total]);

			foreach ($users as $u) {
				try {
					Mail::to($u->email)->send(new CouponEmail($u, $coupon));
					$sent++;
				} catch (Throwable $e) {
					$failed++;
					Log::error('Coupon email send failed', ['batch_id' => $batch->id, 'email' => $u->email, 'error' => $e->getMessage()]);
				}
			}

			$batch->update([
				'sent_count' => $sent,
				'failed_count' => $failed,
				'status' => 'completed',
				'finished_at' => now(),
				'message' => "Completed. Sent: {$sent}, Failed: {$failed}",
			]);
			Log::info('SendCouponBatchJob completed', ['batch_id' => $batch->id, 'sent' => $sent, 'failed' => $failed]);
		} catch (Throwable $e) {
			$batch->update(['status' => 'failed', 'message' => $e->getMessage(), 'finished_at' => now()]);
			Log::error('SendCouponBatchJob failed', ['batch_id' => $batch->id ?? $this->batchId, 'error' => $e->getMessage()]);
			throw $e;
		}
	}
}


