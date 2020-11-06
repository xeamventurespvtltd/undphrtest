<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TravelClaimDetail extends Model
{
     public function expense_types(){
        return $this->belongsTo('App\Conveyance', 'expense_type', 'id');
    }
}
