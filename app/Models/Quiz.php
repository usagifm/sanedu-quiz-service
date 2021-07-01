<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'tbl_quizs';

    protected static function boot() {
        parent::boot();
        static::deleting(function ($data) {
        });
    }

    public function creator() {
        return $this->belongsTo('App\Models\User', 'creator_id')->withDefault();
    }
    public function questions() {
        return $this->hasMany('App\Models\QuizQuestion', 'quiz_id');
    }

    // $data is Array Data
    // $additionalAttribute is Array Data
    public static function mapData($data, $additionalAttribute = null) {
        $result = [
            'id' => $data->id,
            'creator_id' => $data->creator_id,
            'meeting_id' => $data->meeting_id,
            'name' => $data->name,
            'duration' => $data->duration,
            'question_number' => $data->question_number,
            'created_at' => date("Y-m-d H:i:s", strtotime($data->created_at))
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }
    
    public static function mapDetailData($data, $additionalAttribute = null) {
        $result = [
            'id' => $data->id,
            'creator_id' => $data->creator_id,
            'meeting_id' => $data->meeting_id,
            'name' => $data->name,
            'duration' => $data->duration,
            'question_number' => $data->question_number,
            'created_at' => date("Y-m-d H:i:s", strtotime($data->created_at)),
            'questions' => $data->questions->map(function($item) {
                return QuizQuestion::mapData($item);
            })
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }
}
