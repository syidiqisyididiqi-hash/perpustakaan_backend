<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->id();

            
            $table->foreignId('loan_detail_id')
                ->constrained('loan_details')
                ->cascadeOnDelete();
            $table->unsignedInteger('overdue_days')->default(0);
            $table->decimal('daily_rate', 10, 2)->default(5000);
            $table->decimal('total_fine', 12, 2)->default(0);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique('loan_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
