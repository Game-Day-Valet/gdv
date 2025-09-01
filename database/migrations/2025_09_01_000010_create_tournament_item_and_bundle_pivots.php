<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('tournament_item', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('tournament_id');
			$table->unsignedBigInteger('item_id');
			$table->decimal('price', 10, 2)->nullable(); // null => default item price
			$table->timestamps();
			$table->unique(['tournament_id', 'item_id']);
		});

		Schema::create('tournament_bundle', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('tournament_id');
			$table->unsignedBigInteger('bundle_id');
			$table->decimal('price', 10, 2)->nullable(); // null => default bundle price
			$table->timestamps();
			$table->unique(['tournament_id', 'bundle_id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('tournament_bundle');
		Schema::dropIfExists('tournament_item');
	}
};


