<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Knowledge extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $guarded = [];


    public function category(){
        return $this->belongsTo('App\Models\KnowledgeCategory','id_category');
    }
    
    public function ratings()
    {
        return $this->hasMany('App\Models\KnowledgeRating', 'id_knowledge', 'id');
    }


}
