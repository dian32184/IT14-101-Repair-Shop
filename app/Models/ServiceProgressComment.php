<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $report_id
 * @property string $progress_key
 * @property string $comment_text
 * @property int|null $created_by
 * @property string|null $created_by_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ServiceReport $report
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceProgressComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceProgressComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceProgressComment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceProgressComment whereCommentText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceProgressComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceProgressComment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceProgressComment whereCreatedByName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceProgressComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceProgressComment whereProgressKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceProgressComment whereReportId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ServiceProgressComment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ServiceProgressComment extends Model
{
    protected $fillable = [
        'report_id',
        'progress_key',
        'comment_text',
        'created_by',
        'created_by_name',
    ];

    public function report()
    {
        return $this->belongsTo(ServiceReport::class, 'report_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
