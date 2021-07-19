<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttemptCorrection extends Model
{
    protected $table = 'tbl_attempt_corrections';

    protected static function boot() {
        parent::boot();
        static::deleting(function ($data) {
        });
    }

    public function attempt() {
        return $this->belongsTo('App\Models\Attempt', 'attempt_id')->withDefault();
    }
    public function question() {
        return $this->belongsTo('App\Models\QuizQuestion', 'quiz_question_id')->withDefault();
    }

    // $data is Array Data
    // $additionalAttribute is Array Data
    public static function mapData($data, $additionalAttribute = null) {
        $result = [
            'id' => $data->id,
            'attempt_id' => $data->attempt_id,
            'quiz_question_id' => $data->quiz_question_id,
            'answer' => $data->answer,
            'answer_image' => $data->answer_image != null ? env('APP_STORAGE_URL') . $data->answer_image : null,
            'is_correct' => $data->is_correct ? true : false,
            'is_corrected' => $data->is_corrected ? true : false,
            'correct_answer' => $data->question->answer ?? $data->question->essay_answer
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }
    
}
