<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'total_hours',
        'regular_hours',
        'overtime_hours',
        'hourly_rate',
        'regular_pay',
        'overtime_pay',
        'total_pay',
        'deductions',
        'net_pay',
        'status',
        'generated_at',
        'paid_at',
    ];

    protected $casts = [
        'total_hours' => 'decimal:2',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'regular_pay' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'total_pay' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'generated_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the user that owns the payslip.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get month name
     */
    public function getMonthNameAttribute(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F');
    }

    /**
     * Get formatted total pay
     */
    public function getFormattedTotalPayAttribute(): string
    {
        return '$' . number_format($this->total_pay, 2);
    }

    /**
     * Get formatted net pay
     */
    public function getFormattedNetPayAttribute(): string
    {
        return '$' . number_format($this->net_pay, 2);
    }

    /**
     * Check if payslip is generated
     */
    public function isGenerated(): bool
    {
        return $this->status === 'generated';
    }

    /**
     * Check if payslip is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Mark payslip as generated
     */
    public function markAsGenerated(): void
    {
        $this->update([
            'status' => 'generated',
            'generated_at' => now(),
        ]);
    }

    /**
     * Mark payslip as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Calculate payslip from time records
     */
    public static function calculateFromTimeRecords(User $user, int $month, int $year): array
    {
        $timeRecords = $user->timeRecords()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $totalHours = $timeRecords->sum('total_hours');
        $regularHours = min($totalHours, 160); // 8 hours * 20 working days
        $overtimeHours = max(0, $totalHours - 160);
        
        $regularPay = $regularHours * $user->hourly_rate;
        $overtimePay = $overtimeHours * ($user->hourly_rate * 1.5); // 1.5x for overtime
        $totalPay = $regularPay + $overtimePay;
        $deductions = 0; // Can be calculated based on company policy
        $netPay = $totalPay - $deductions;

        return [
            'total_hours' => $totalHours,
            'regular_hours' => $regularHours,
            'overtime_hours' => $overtimeHours,
            'hourly_rate' => $user->hourly_rate,
            'regular_pay' => $regularPay,
            'overtime_pay' => $overtimePay,
            'total_pay' => $totalPay,
            'deductions' => $deductions,
            'net_pay' => $netPay,
        ];
    }
}
