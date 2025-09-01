<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('coupon_send_batches', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('coupon_id');
			$table->unsignedBigInteger('initiated_by');
			$table->unsignedInteger('total_recipients')->default(0);
			$table->unsignedInteger('sent_count')->default(0);
			$table->unsignedInteger('failed_count')->default(0);
			$table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');
			$table->text('message')->nullable();
			$table->timestamp('started_at')->nullable();
			$table->timestamp('finished_at')->nullable();
			$table->timestamps();

			$table->index(['coupon_id', 'status']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('coupon_send_batches');
	}
};


