<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CaseStudy extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $guarded = [];

    public function client(){
        return $this->belongsTo('App\Models\Client','id_client');
    }
    
    public function campaign(){
        return $this->belongsTo('App\Models\Campaign','id_campaign');
    }
}
