<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TravelClaim extends Model
{
    function claim_attachments()
    {
    	return $this->hasMany('App\TravelClaimAttachment');
    }
    function claim_details()
    {
    	return $this->hasMany('App\TravelClaimDetail');
    }
    
}
