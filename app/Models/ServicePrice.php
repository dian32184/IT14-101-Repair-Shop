<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $service_name
 * @property numeric $service_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePrice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePrice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePrice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePrice whereServiceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePrice whereServicePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServicePrice whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServicePrice extends Model
{
    protected $fillable = ['service_name', 'service_price'];
}
