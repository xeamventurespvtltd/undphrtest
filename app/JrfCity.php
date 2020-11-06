<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JrfCity extends Model
{

	protected $guarded 		= [];
    protected $table	 	= 'city_jrf';

    /*protected $guarded = [];

    function users()
    {
        return $this->belongsToMany('App\User')->withTimestamps();
    }
    
    function jrf()
    {
        return $this->hasOne('App\Jrf')->withTimestamps();
    }*/

}