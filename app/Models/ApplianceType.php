<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplianceType extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function commonProblems()
    {
        return $this->hasMany(CommonProblem::class);
    }
}
