<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ChatQuestion extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    public function question_label()
    {
        return $this->belongsTo('App\Models\ChatQuestionLabel', 'id_question_label');
    }

    public function answer_type()
    {
        return $this->belongsTo('App\Models\ChatQuestionType', 'id_answer_type');
    }
}
