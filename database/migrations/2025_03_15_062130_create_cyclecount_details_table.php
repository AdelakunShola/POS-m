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
        Schema::create('cyclecount_details', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cycle_count_id');
                $table->string('scanned_barcode')->nullable(); // Store scanned barcode
                $table->integer('expected_stock');
                $table->integer('counted_qty');
                $table->decimal('value_diff', 10, 2);
                
                $table->boolean('recount')->default(false); // Checkbox for recount
                $table->boolean('approved')->default(false); // Checkbox for approval
                
                $table->unsignedBigInteger('counted_by')->nullable(); // User who counted
                $table->unsignedBigInteger('approved_by')->nullable(); // User who approved
                $table->text('note')->nullable(); // Additional remarks
                
                $table->timestamps();
            
                // Foreign keys
                $table->foreign('cycle_count_id')->references('id')->on('cycle_counts')->onDelete('cascade');
                $table->foreign('counted_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });
            
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cyclecount_details');
    }
};
