<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    protected $table = 'tbl_attempts';

    protected static function boot() {
        parent::boot();
        static::deleting(function ($data) {
        });
    }

    public function quiz() {
        return $this->belongsTo('App\Models\Quiz', 'quiz_id')->withDefault();
    }
    public function student() {
        return $this->belongsTo('App\Models\User', 'student_id')->withDefault();
    }
    public function corrections() {
        return $this->hasMany('App\Models\AttemptCorrection', 'attempt_id');
    }

    // $data is Array Data
    // $additionalAttribute is Array Data
    public static function mapData($data, $additionalAttribute = null) {
        $result = [
            'id' => $data->id,
            'quiz_id' => $data->quiz_id,
            'student_id' => $data->student_id,
            'started_at' => date("Y-m-d H:i:s", strtotime($data->started_at)),
            'finished_at' => date("Y-m-d H:i:s", strtotime($data->finished_at)),
            'correct_number' => $data->correct_number,
            'incorrect_number' => $data->incorrect_number,
            'not_answered_number' => $data->not_answered_number,
            'score' => $data->score,
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }
    
    // $data is Array Data
    // $additionalAttribute is Array Data
    public static function mapDetailData($data, $additionalAttribute = null) {
        $result = [
            'id' => $data->id,
            'quiz_id' => $data->quiz_id,
            'quiz' => Quiz::mapDetailData($data->quiz),
            'student_id' => $data->student_id,
            'student_name' => $data->student->name,
            'student_school' => $data->student->school,
            'started_at' => date("Y-m-d H:i:s", strtotime($data->started_at)),
            'finished_at' => date("Y-m-d H:i:s", strtotime($data->finished_at)),
            'correct_number' => $data->correct_number,
            'incorrect_number' => $data->incorrect_number,
            'not_answered_number' => $data->not_answered_number,
            'score' => $data->score,
            'corrections' => $data->corrections->map(function($item) {
                return AttemptCorrection::mapData($item);
            }),
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }
}
