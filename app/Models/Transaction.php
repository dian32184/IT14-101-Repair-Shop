<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $report_id
 * @property numeric|null $parts_total
 * @property numeric|null $labor_total
 * @property numeric|null $total_amount
 * @property numeric|null $partial_payment_amount
 * @property numeric|null $amount_paid
 * @property string|null $payment_status
 * @property string|null $payment_method
 * @property string|null $reference_no
 * @property string|null $receipt_no
 * @property \Illuminate\Support\Carbon|null $payment_date
 * @property \Illuminate\Support\Carbon|null $payment_due
 * @property string|null $received_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $deleted_by
 * @property string|null $deletion_reason
 * @property string|null $paymongo_link_id
 * @property string|null $payment_url
 * @property-read \App\Models\ServiceReport|null $report
 */
class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'report_id',
        'parts_total',
        'labor_total',
        'total_amount',
        'partial_payment_amount',
        'amount_paid',
        'payment_status',
        'payment_method',
        'reference_no',
        'receipt_no',
        'payment_date',
        'payment_due',
        'received_by',
        'paymongo_link_id',
        'payment_url',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'payment_due' => 'date',
        'total_amount' => 'decimal:2',
        'partial_payment_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'parts_total' => 'decimal:2',
        'labor_total' => 'decimal:2',
    ];

    public function report()
    {
        return $this->belongsTo(ServiceReport::class, 'report_id');
    }

    /**
     * Money received in this payment event (supports legacy rows).
     */
    public function amountPaidThisPayment(): float
    {
        if ($this->amount_paid !== null) {
            return (float) $this->amount_paid;
        }

        if ($this->payment_status === 'Partial') {
            return (float) ($this->partial_payment_amount ?? 0);
        }

        if ($this->payment_status === 'Unpaid') {
            return 0.0;
        }

        return (float) ($this->total_amount ?? 0);
    }

    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'Paid';
    }

    public function isLocked(): bool
    {
        // Recorded payments are immutable — use Add Payment for remaining balance
        if (in_array($this->payment_status, ['Paid', 'Partial'], true)) {
            return true;
        }

        // Once the job is fully paid, earlier rows stay locked too
        if ($this->report_id) {
            return static::where('report_id', $this->report_id)
                ->whereIn('payment_status', ['Paid', 'Partial'])
                ->exists();
        }

        return false;
    }

    public static function generateReceiptNo(): string
    {
        $prefix = 'RCP-' . now()->format('Ymd');
        $count = static::withTrashed()
            ->whereDate('created_at', today())
            ->count() + 1;

        return $prefix . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Total already paid across all payments for a service report.
     */
    public static function totalPaidForReport(int $reportId, ?int $excludeTransactionId = null): float
    {
        $query = static::where('report_id', $reportId);
        if ($excludeTransactionId) {
            $query->where('id', '!=', $excludeTransactionId);
        }

        return (float) $query->get()->sum(fn (self $t) => $t->amountPaidThisPayment());
    }
}
