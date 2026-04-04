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
 * @property string|null $payment_status
 * @property \Illuminate\Support\Carbon|null $payment_date
 * @property string|null $received_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $deleted_by
 * @property string|null $deletion_reason
 * @property string|null $paymongo_link_id
 * @property string|null $payment_url
 * @property-read \App\Models\ServiceReport|null $report
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereDeletionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereLaborTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePartsTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymentUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePaymongoLinkId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereReceivedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction withoutTrashed()
 * @mixin \Eloquent
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
        'payment_status',
        'payment_method',
        'reference_no',
        'payment_date',
        'payment_due',
        'received_by',
        'paymongo_link_id',
        'payment_url'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'payment_due' => 'date',
    ];

    public function report()
    {
        return $this->belongsTo(ServiceReport::class , 'report_id');
    }
}
