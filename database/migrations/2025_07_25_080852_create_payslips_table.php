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
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('month');
            $table->integer('year');
            $table->decimal('total_hours', 10, 2);
            $table->decimal('regular_hours', 10, 2)->default(0.00);
            $table->decimal('overtime_hours', 10, 2)->default(0.00);
            $table->decimal('hourly_rate', 10, 2);
            $table->decimal('regular_pay', 12, 2);
            $table->decimal('overtime_pay', 12, 2)->default(0.00);
            $table->decimal('total_pay', 12, 2);
            $table->decimal('deductions', 12, 2)->default(0.00);
            $table->decimal('net_pay', 12, 2);
            $table->enum('status', ['draft', 'generated', 'paid'])->default('draft');
            $table->datetime('generated_at')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
