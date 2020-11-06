<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TravelOtherApproval extends Model
{
    public function project()
    {
        return $this->morphedByMany('App\Project', 'travel_other_approvalable');
    }
    public function city(){
        return $this->belongsTo('App\City');
    }
}
