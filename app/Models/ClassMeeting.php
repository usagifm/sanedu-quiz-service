<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassMeeting extends Model
{
    protected $table = 'tbl_class_meetings';

    protected static function boot() {
        parent::boot();
        static::deleting(function ($data) {
        });
    }

    public function quiz() {
        return $this->hasOne('App\Models\Quiz', 'meeting_id');
    }
    public function lessons() {
        return $this->hasMany('App\Models\ClassMeetingLesson', 'class_meeting_id');
    }

    // $data is Array Data
    // $additionalAttribute is Array Data
    public static function mapData($data, $additionalAttribute = null) {
        $result = [
            'id' => $data->id,
            'class_id' => $data->class_id,
            'name' => $data->name,
            'date' => $data->date,
            'start_time' => $data->start_time,
            'finish_time' => $data->finish_time
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }
    
    public static function mapDetailData($data, $additionalAttribute = null) {
        $result = [
            'id' => $data->id,
            'class_id' => $data->class_id,
            'name' => $data->name,
            'date' => $data->date,
            'start_time' => $data->start_time,
            'finish_time' => $data->finish_time,
            'lessons' => $data->lessons->map(function($item) {
                return ClassMeetingLesson::mapData($item);
            }),
            'quiz' => $data->quiz ? Quiz::mapData($data->quiz) : null,
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }
}
