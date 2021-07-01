<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    protected $table = 'tbl_quiz_questions';

    protected static function boot() {
        parent::boot();
        static::deleting(function ($data) {
        });
    }

    public function quiz() {
        return $this->belongsTo('App\Models\Quiz', 'quiz_id')->withDefault();
    }

    // $data is Array Data
    // $additionalAttribute is Array Data
    public static function mapData($data, $additionalAttribute = null) {
        $result = [
            'id' => $data->id,
            'quiz_id' => $data->quiz_id,
            'question_type' => $data->question_type,
            'question_type_label' => $data->question_type == 1 ? "multiple_choice" : "esay",
            'question' => $data->question,
            'question_image' => $data->question_image != null ? env('APP_STORAGE_URL') . $data->question_image : null,
            'a' => $data->a,
            'b' => $data->b,
            'c' => $data->c,
            'd' => $data->d,
            'e' => $data->e,
            'answer' => $data->answer,
            'essay_answer' => $data->essay_answer,
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }
}
