<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassMeetingLesson extends Model
{
    protected $table = 'tbl_class_meeting_lessons';

    protected static function boot() {
        parent::boot();
        static::deleting(function ($data) {
        });
    }

    // $data is Array Data
    // $additionalAttribute is Array Data
    public static function mapData($data, $additionalAttribute = null) {
        $result = [
            'id' => $data->id,
            'class_meeting_id' => $data->class_meeting_id,
            'name' => $data->name,
            'link' => $data->link,
            'youtube' => $data->youtube,
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }
}
