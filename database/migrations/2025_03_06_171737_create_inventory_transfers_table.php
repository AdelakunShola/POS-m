<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('barcode');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('from_location_id')->constrained('locations')->onDelete('cascade');
            $table->string('from_location_line_id')->nullable();
            $table->foreignId('to_location_id')->constrained('locations')->onDelete('cascade');
            $table->string('to_location_line_id')->nullable();
            $table->integer('quantity');
            $table->timestamp('transfer_date')->useCurrent();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transfers');
    }
};
