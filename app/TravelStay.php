<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TravelStay extends Model
{
    public function project(){
        return $this->morphTo();
    }
    public function city(){
        return $this->belongsTo('App\City');
    }
}
