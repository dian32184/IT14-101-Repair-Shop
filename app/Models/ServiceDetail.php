<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $report_id
 * @property array<array-key, mixed> $service_types
 * @property numeric $service_charge
 * @property \Illuminate\Support\Carbon|null $date_repaired
 * @property \Illuminate\Support\Carbon|null $date_delivered
 * @property string|null $complaint
 * @property numeric $labor
 * @property numeric $pullout_delivery
 * @property numeric $parts_total_charge
 * @property numeric $total_amount
 * @property string|null $receptionist
 * @property string|null $manager
 * @property string|null $technician
 * @property string|null $released_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ServiceReport $report
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereComplaint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereDateDelivered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereDateRepaired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereLabor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereManager($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail wherePartsTotalCharge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail wherePulloutDelivery($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereReceptionist($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereReleasedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereServiceCharge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereServiceTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereTechnician($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceDetail whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceDetail extends Model
{
    protected $fillable = [
        'report_id',
        'service_types',
        'service_charge',
        'date_repaired',
        'date_delivered',
        'complaint',
        'labor',
        'pullout_delivery',
        'parts_total_charge',
        'miscellaneous_cost',
        'total_amount',
        'receptionist',
        'manager',
        'technician',
        'released_by'
    ];

    protected $casts = [
        'service_types' => 'array',
        'date_repaired' => 'date',
        'date_delivered' => 'date',
    ];

    public function report()
    {
        return $this->belongsTo(ServiceReport::class , 'report_id');
    }
}
