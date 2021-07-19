<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\ClassMeeting;
use App\Models\ClassModel;
use App\Models\ClassStudent;
use App\Models\ClassMeetingLesson;
use App\Models\ClassLessonAttendees;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassController extends Controller
{

    public function getAssignedClass()
    {
        $perPage = $_GET['per_page'] ?? 10;
        $page = isset($_GET['page']) ? $_GET['page'] * $perPage : 0;

        $class = User::where("id", Auth::user()->id)
            ->with(['classes'])
            ->offset($page)
            ->limit($perPage)
            ->first();
        if (!$class) return $this->responseError("Anda Tidak Terdaftar di Kelas Manapun");

        $data = $class->classes->map(function ($item) {
            return ClassModel::mapData($item);
        });
        return $this->responseOK($data);
    }

    public function detailAssignedClass($id)
    {
        $class = ClassStudent::where("student_id", Auth::user()->id)
            ->where("class_id", $id)
            ->first();

        if (!$class) return $this->responseError("Anda Tidak Terdaftar di Kelas Ini ");

        $detail = ClassModel::where("id", $id)
            ->with(['students', 'meetings'])
            ->first();
        return $this->responseOK(ClassModel::mapData($detail));
    }

    public function listMeeting($id)
    {
        $class = ClassStudent::where("student_id", Auth::user()->id)
            ->where("class_id", $id)
            ->first();
        if (!$class) return $this->responseError("Anda Tidak Terdaftar di Kelas Ini ");

        $classCheck = ClassModel::where("id", $id)->first();
        if (!$classCheck) return $this->responseError("Kelas Tidak Tersedia");

        $list = ClassMeeting::where("class_id", $id)
            ->with(['lessons', 'quiz'])
            ->get();
        if (!$list) return $this->responseError("Belum Ada Pertemuan yang Tersedia");
        return $this->responseOK($list);
    }

    public function detailMeeting($id, $meetId) {
        $class = ClassStudent::where("student_id", Auth::user()->id)
            ->where("class_id", $id)
            ->first();
        if (!$class) return $this->responseError("Anda Tidak Terdaftar di Kelas Ini");

        $data = ClassMeeting::where("id", $meetId)->where("class_id", $id)->first();
        if (!$data) return $this->responseError("Pertemuan Tidak ada");

        if($data->quiz) {
            $attempt = Attempt::where("quiz_id", $data->quiz->id)
                            ->where("student_id", Auth::user()->id)
                            ->first();
            if($attempt)  {
                $now = date("Y-m-d H:i:s");
                $arr = ['attempt' => Attempt::mapData($attempt)];
                if($now >= date("Y-m-d H:i:s", strtotime($attempt->finished_at)))
                    return $this->responseOK(ClassMeeting::mapDetailData($data, $arr));
            }
        }

        return $this->responseOK(ClassMeeting::mapDetailData($data));
    }

    public function detailLesson($id , $meetId, $lessonId) {
        $class = ClassStudent::where("student_id", Auth::user()->id)
            ->where("class_id", $id)
            ->first();
        if (!$class) return $this->responseError("Anda Tidak Terdaftar di Kelas Ini");

        $meeting = ClassMeeting::where("id", $meetId)->where("class_id", $id)->first();
        if (!$meeting) return $this->responseError("Pertemuan Tidak ada");

        $lesson = ClassMeetingLesson::where("class_meeting_id",$meetId)
            ->where("id",$lessonId)
            ->first();
        if (!$lesson) return $this->responseError("Materi Tidak Ada !");
        return $this->responseOK(ClassMeetingLesson::mapData($lesson));
    }

    public function searchClass(Request $request) {
        $class = ClassModel::where("class_code", $request->class_code)
            ->with(['students', 'meetings'])
            ->first();
        if (!$class) return $this->responseError("Kelas Tidak Ada ");
        return $this->responseOK($class);
    }

    public function registerClass($id) {
        $class = ClassModel::where("id", $id)->first();
        if (!$class) return $this->responseError("Kelas Tidak Ada");

        $assignCheck = ClassStudent::where("student_id", Auth::user()->id)
            ->where("class_id", $id)
            ->first();
        if ($assignCheck) return $this->responseError("Anda Sudah Terdaftar di Kelas Ini");

        $quotaCheck = ClassStudent::where("class_id" , $class->id)->get()->count();
        if ($quotaCheck >= $class->quota) return $this->responseError("Kuota Kelas Sudah Penuh !");

        $assign = new ClassStudent;
        $assign->class_id = $class->id;
        $assign->student_id = Auth::user()->id;
        $assign->save();
        return $this->responseOK(ClassStudent::findOrFail($assign->id));
    }


    public function attendees($lessonId) {
        $lesson = ClassMeetingLesson::where("id", $lessonId)->first();
        if (!$lesson) return $this->responseError("Materi Tidak Ada !");

        $meeting = ClassMeeting::where('id', $lesson->class_meeting_id)->first();
        if (!$meeting) return $this->responseError("Pertemuan Tidak Ada !");

        $class = ClassModel::where('id', $meeting->class_id)->first();
        if (!$class) return $this->responseError("Kelas Tidak Ada !");

        $assignCheck = ClassStudent::where('class_id' , $class->id)
            ->where('student_id' , Auth::user()->id)
            ->first();
        if (!$assignCheck) return $this->responseError("Anda Tidak Terdaftar Dalam Kelas Ini !");

        $attendedCheck = ClassLessonAttendees::where('student_id' ,  Auth::user()->id)
            ->where('class_id' , $class->id)
            ->where('class_meeting_id' , $meeting->id)
            ->where('lesson_id' , $lessonId)
            ->first();
        if ($attendedCheck) return $this->responseOK("OK");
    
        $attend = new ClassLessonAttendees;
        $attend->class_id = $class->id;
        $attend->class_meeting_id = $meeting->id;
        $attend->lesson_id = $lessonId;
        $attend->student_id = $assignCheck->student_id;
        $attend->save();
        return $this->responseOK("OK");
    }

    public function resignClass($id) {
        $assignCheck = ClassStudent::where("student_id", Auth::user()->id)
            ->where("class_id", $id)
            ->first();
        if (!$assignCheck) return $this->responseError("Anda Tidak Terdaftar di Kelas Ini !");

        if($assignCheck) {
            if($assignCheck->delete()) return $this->responseOK("Berhasil Keluar dari Kelas Ini ! ");
            return $this->responseError("Gagal keluar !");
        }
        return $this->responseError("Data Tidak ditemukan ! ");
    }

}