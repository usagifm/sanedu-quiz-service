<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassLessonAttendees extends Model
{
    protected $table = 'tbl_class_meeting_attendees';

    protected static function boot() {
        parent::boot();
        static::deleting(function ($data) {
        });
    }

    // $data is Array Data
    // $additionalAttribute is Array Data
    public static function mapData($data, $additionalAttribute = null) {
        $result = [
            'id' => $data->id ?? "",
            'class_id' => $data->class_id,
            'class_meeting_id' => $data->class_meeting_id,
            'lesson_id' => $data->lesson_id,
            'student_id' => $data->student_id
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }
}
