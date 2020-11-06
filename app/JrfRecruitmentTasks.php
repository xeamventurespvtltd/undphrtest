<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JrfRecruitmentTasks extends Model
{
    protected $guarded = [];
    protected $table = 'jrf_recruitment_tasks';

    function user()
    {
    	return $this->belongsTo('App\User');
    }

    function jrf(){

        return $this->hasOne('App\Jrf');
    }
}