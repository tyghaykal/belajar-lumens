<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ChatQuestionType extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function answer()
    {
        return $this->hasMany('App\Models\ChatAnswer', 'id_question_types', 'id');
    }
}
