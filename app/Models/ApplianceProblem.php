<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplianceProblem extends Model
{
    protected $fillable = [
        'appliance_id',
        'common_problem_id',
        'other_problem',
    ];

    public function appliance()
    {
        return $this->belongsTo(Appliance::class);
    }

    public function commonProblem()
    {
        return $this->belongsTo(CommonProblem::class);
    }
}
