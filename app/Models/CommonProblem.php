<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommonProblem extends Model
{
    protected $fillable = [
        'appliance_type_id',
        'problem_name',
    ];

    public function applianceType()
    {
        return $this->belongsTo(ApplianceType::class);
    }

    public function applianceProblems()
    {
        return $this->hasMany(ApplianceProblem::class);
    }
}
