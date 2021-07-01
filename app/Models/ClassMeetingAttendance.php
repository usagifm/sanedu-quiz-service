<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassMeetingAttendance extends Model
{
    protected $table = 'tbl_class_meeting_attendees';

    protected static function boot() {
        parent::boot();
        static::deleting(function ($data) {
        });
    }
}
