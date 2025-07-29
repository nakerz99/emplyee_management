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
        Schema::create('break_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_record_id')->constrained()->onDelete('cascade');
            $table->datetime('break_start');
            $table->datetime('break_end')->nullable();
            $table->decimal('total_break_time', 8, 2)->default(0.00);
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('break_sessions');
    }
};
