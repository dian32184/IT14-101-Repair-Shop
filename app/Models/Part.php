<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string|null $part_no
 * @property string|null $name
 * @property numeric|null $price
 * @property int|null $quantity_stock
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $deleted_by
 * @property string|null $deletion_reason
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ServiceReport> $serviceReports
 * @property-read int|null $service_reports_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part whereDeletionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part wherePartNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part whereQuantityStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Part withoutTrashed()
 * @mixin \Eloquent
 */
class Part extends Model
{
    use SoftDeletes;
    protected $fillable = ['part_no', 'name', 'price', 'quantity_stock'];

    public function serviceReports()
    {
        return $this->belongsToMany(ServiceReport::class)
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }
}
