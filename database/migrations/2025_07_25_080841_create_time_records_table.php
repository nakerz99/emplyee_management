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
        Schema::create('time_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->datetime('clock_in')->nullable();
            $table->datetime('clock_out')->nullable();
            $table->decimal('total_hours', 8, 2)->default(0.00);
            $table->decimal('break_hours', 8, 2)->default(0.00);
            $table->decimal('overtime_hours', 8, 2)->default(0.00);
            $table->enum('status', ['active', 'completed', 'absent'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_records');
    }
};
