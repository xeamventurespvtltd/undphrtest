<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Conveyance extends Model
{
    function bands()
    {
        return $this->belongsToMany('App\Band');
    }
}
