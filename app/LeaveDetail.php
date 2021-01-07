<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LeaveDetail extends Model
{
//    Protected $table = 'leave_pools';
    protected $guarded = [];

    function user()
    {
    	return $this->belongsTo('App\User');
    }


}
