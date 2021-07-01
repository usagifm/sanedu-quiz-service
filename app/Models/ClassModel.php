<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    protected $table = 'tbl_classes';

    protected static function boot() {
        parent::boot();
        static::deleting(function ($data) {
        });
    }

    public function creator() {
        return $this->belongsTo('App\Models\User', 'creator_id')->withDefault();
    }

    public function meetings() {
        return $this->hasMany('App\Models\ClassMeeting', 'class_id');
    }


    public function students() {
        return $this->belongsToMany('App\Models\User', 'tbl_class_students', 'class_id', 'student_id');
  	}

    // $data is Array Data
    // $additionalAttribute is Array Data
    public static function mapData($data, $additionalAttribute = null) {
        $result = [
            'id' => $data->id,
            'creator_id' => $data->creator_id,
            'name' => $data->name,
            'class_code' => $data->class_code,
            'teacher_name' => $data->creator->name,
            'teacher_whatsapp' => $data->creator->whatsapp,
            'quota' => $data->quota,
            'student_number' => $data->students->count(),
            'grade_level' => $data->grade_level,
            'school' => $data->school,
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
            'name' => $data->name,
            'class_code' => $data->class_code,
            'teacher_name' => $data->creator->name,
            'teacher_whatsapp' => $data->creator->whatsapp,
            'quota' => $data->quota,
            'student_number' => $data->students->count(),
            'grade_level' => $data->grade_level,
            'school' => $data->school,
            'students' => $data->students->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'whatsapp' => $item->whatsapp,
                    'photo' => $item->photo,
                    'school' => $item->school,
                    'grade_level' => $item->grade_level,
                ];
            }),
            'meeting_schedules' => $data->meetings->map(function($item) {
                return ClassMeeting::mapData($item);
            }),
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }
}
