<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JrfSkill extends Model
{
	
    protected $guarded 		= [];
    protected $table	 	= 'jrf_skill';

    /*function users()
    {
        return $this->belongsToMany('App\User')->withTimestamps();
    }

    function jrf(){

        return $this->hasOne('App\Jrf')->withTimestamps();
    }*/
}
