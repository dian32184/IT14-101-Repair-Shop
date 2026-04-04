<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $staff_id
 * @property string $comment_text
 * @property int $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $creator
 * @property-read \App\Models\User $staff
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffComment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffComment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffComment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffComment whereCommentText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffComment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffComment whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffComment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffComment whereStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StaffComment whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StaffComment extends Model
{
    protected $fillable = [
        'staff_id',
        'comment_text',
        'created_by',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
