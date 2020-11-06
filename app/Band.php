<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Band extends Model
{
    function city_class()
    {
        return $this->belongsToMany('App\CityClass')->withPivot('price');
    }
    function conveyances()
    {
        return $this->belongsToMany('App\Conveyance');
    }
    function local_conveyances()
    {
        return $this->belongsToMany('App\Conveyance')->where('conveyances.islocal', 1);
    }
    function travel_conveyances()
    {
        return $this->belongsToMany('App\Conveyance')->where('conveyances.islocal', 0);
    }
}
