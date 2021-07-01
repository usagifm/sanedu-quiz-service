<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassStudent extends Model
{
    protected $table = 'tbl_class_students';

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
            'class_id' => $data->class_id,
            'name' => $data->name,
            'student_id' => $data->student_id,
            'date' => $data->date,
            'start_time' => $data->start_time,
            'finish_time' => $data->finish_time
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }
}
