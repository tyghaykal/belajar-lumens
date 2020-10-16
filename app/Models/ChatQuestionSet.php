<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ChatQuestionSet extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    public function ChatQuestion()
    {
        return $this->hasMany('App\Models\ChatQuestion', 'id_question_label', 'id_question_label');
    }

}
