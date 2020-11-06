<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JrfApprovals extends Model
{
    protected $guarded = [];

    function user()
    {
    	return $this->belongsTo('App\User');
    }

    function jrf(){

        return $this->hasOne('App\Jrf');
    }

    //  function JrfApprovals(){

    //     return $this->hasMany('App\JrfApprovals');
    // }

    function notifications()
    {
        return $this->morphMany('App\Notification', 'notificationable');
    }
}
