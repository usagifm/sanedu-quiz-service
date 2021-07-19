<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\ClassMeeting;
use App\Models\ClassMeetingAttendance;
use App\Models\ClassMeetingLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassModel;
use App\Models\ClassStudent;
use App\Models\Quiz;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClassController extends Controller
{
    
    public function classes() {
        $perPage = $_GET['per_page'] ?? 10;
        $page = isset($_GET['page']) ? $_GET['page']*$perPage : 0;

        $class = ClassModel::where("creator_id", Auth::user()->id)
                    // ->offset($page)
                    // ->limit($perPage)
                    ->get();
        $data = $class->map(function($item){
            return ClassModel::mapData($item);
        });
        return $this->responseOK($data);
    }

    public function detail($id) {
        $class = ClassModel::where("creator_id", Auth::user()->id)
                    ->where("id", $id)
                    ->with(['students', 'meetings'])
                    ->first();
        if(!$class) return $this->responseError("Kelas tidak ada");

        $data = $class;
        return $this->responseOK(ClassModel::mapDetailData($data));
    }

    function uniqidReal($lenght = 13) {
        if (function_exists("random_bytes")) {
            $bytes = random_bytes(ceil($lenght / 2));
        } elseif (function_exists("openssl_random_pseudo_bytes")) {
            $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } else {
            throw new Exception("no cryptographically secure random function available");
        }
        return substr(bin2hex($bytes), 0, $lenght);
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
            'quota' => 'required|numeric|min:1',
        ]);
        if ($validator->fails()) return $this->responseInvalidInput($validator->errors());

        $code = $this->uniqidReal(6);
        $check = ClassModel::where("class_code", $code)->first();
        while($check) {
            $code = $this->uniqidReal(6);
            $check = ClassModel::where("class_code", $code)->first();
        }

        $class              = new ClassModel;
        $class->creator_id  = Auth::user()->id;
        $class->name        = $request->name;
        $class->class_code  = $this->uniqidReal(6);
        $class->grade_level = $request->grade_level;
        $class->school      = $request->school;
        $class->quota       = $request->quota;
        $class->save();

        return $this->responseOK(ClassModel::mapDetailData($class));
    }
    
    public function update(Request $request, $id) {
        $class              = ClassModel::find($id);
        if(!$class) 
            return $this->responseError("Data tidak ditemukan");
        if($class->creator_id != Auth::user()->id)
            return $this->responseError("Anda tidak diizinkan mengakses");

        $class->name            = $request->name;
        $class->grade_level     = $request->grade_level;
        $class->school          = $request->school;
        $class->quota           = $request->quota;
        $class->save();
        return $this->responseOK(ClassModel::mapDetailData($class));
    }


    public function editClassCode(Request $request, $id) {
        $class              = ClassModel::find($id);
        if(!$class) 
            return $this->responseError("Data tidak ditemukan");
        if($class->creator_id != Auth::user()->id)
            return $this->responseError("Anda tidak diizinkan mengakses");

        $class->class_code            = $request->class_code;
        $class->save();
        return $this->responseOK(ClassModel::mapDetailData($class));
    }


    public function delete($id) {
        $class = ClassModel::find($id);
        if($class) {
            if($class->delete())
                return $this->responseOK("DELETED");
            return $this->responseError("REMOVING FAILED");
        }
        return $this->responseError("DATA NOT FOUND");
    }

    public function deleteStudent($id, $studentId) {
        $class = ClassModel::find($id);
        if(!$class) return $this->responseError("CLASS NOT FOUND");

        $student = ClassStudent::where("class_id", $class->id)->where("student_id", $studentId)->first();
        if(!$student) return $this->responseError("Siswa Tidak Terdaftar");

        if($student->delete()) return $this->responseOK("OK");

        return $this->responseError("Error");
    }

    public function meetings($id) {
        $perPage = $_GET['per_page'] ?? 10;
        $page = isset($_GET['page']) ? $_GET['page']*$perPage : 0;

        $class = ClassMeeting::where("class_id", $id)
                    ->offset($page)
                    ->limit($perPage)
                    ->get();
        $data = $class->map(function($item){
            return ClassMeeting::mapData($item);
        });
        return $this->responseOK($data);
    }

    public function createClassMeeting(Request $request, $id) {
        $data  = ClassModel::where("id", $id)
                    ->where("creator_id", Auth::id())
                    ->first();
        if(!$data) return $this->responseError("Kelas tidak ada");

        $data              = new ClassMeeting;
        $data->class_id    = $id;
        $data->name        = $request->name;
        $data->date        = $request->date;
        $data->start_time  = $request->start_time;
        $data->finish_time = $request->finish_time;
        $data->save();

        return $this->responseOK(ClassMeeting::mapDetailData($data));
    }

    public function editClassMeeting(Request $request, $id, $meetingId) {
        $class  = ClassModel::where("id", $id)
                    ->where("creator_id", Auth::id())
                    ->first();
        if(!$class) return $this->responseError("Kelas tidak ada");

        $data   = ClassMeeting::where("id", $meetingId)
                                ->where("class_id", $id)
                                ->first();
        if(!$data) return $this->responseError("Data Not Found");

        $data->name        = $request->name;
        $data->date        = $request->date;
        $data->start_time  = $request->start_time;
        $data->finish_time = $request->finish_time;
        $data->save();

        return $this->responseOK(ClassMeeting::mapDetailData($data));
    }

    public function deleteClassMeeting($id, $meetingId) {
        $data  = ClassModel::where("id", $id)
                    ->where("creator_id", Auth::id())
                    ->first();
        if(!$data) return $this->responseError("Kelas tidak ada");

        $class = ClassMeeting::where("id", $meetingId)
                    ->where("class_id", $id)
                    ->first();
        if($class) {
            if($class->delete())
                return $this->responseOK("Berhasil menghapus");
            return $this->responseError("Gagal menghapus");
        }
        return $this->responseError("Kelas tidak ada");
    }

    public function detailMeeting($id, $meetingId) {
        $class  = ClassModel::where("id", $id)
                    ->where("creator_id", Auth::id())
                    ->first();
        if(!$class) return $this->responseError("Kelas tidak ada");

        $meeting   = ClassMeeting::where("id", $meetingId)
                    ->where("class_id", $id)
                    ->first();
        if(!$meeting) return $this->responseError("Pertemuan Tidak ada");

        $attendees = $class->students->map(function($student) use ($meeting) {
            $result = [
                'id' => $student->id,
                'name' => $student->name,
                'photo' => $student->photo,
                'whatsapp' => $student->whatsapp,
                'attendance' => []
            ];
            foreach($meeting->lessons as $val) {
                $present = ClassMeetingAttendance::where("lesson_id", $val->id)
                                ->where("student_id", $student->id)
                                ->first();
                $result['attendance'][] = [
                    'type' => 'lesson',
                    'lesson' => $val->name,
                    'quiz' => null,
                    'is_present' => (bool) $present,
                    'time' => $present ? $present->created_at : null
                ];
            }
            if($meeting->quiz) {
                $present = Attempt::where("quiz_id", $meeting->quiz->id)
                                ->where("student_id", $student->id)
                                ->first();
                $result['attendance'][] = [
                    'type' => 'quiz',
                    'lesson' => null,
                    'quiz' => $meeting->quiz->name,
                    'is_present' => (bool) $present,
                    'time' => $present ? $present->created_at : null
                ];
            }
            return $result;
        });
        
        $attempts = [];
        if($meeting->quiz) {
            $atmps = Attempt::where("quiz_id", $meeting->quiz->id)->orderBy("score", "desc")->get();
            $now = date("Y-m-d H:i:s");
            foreach($atmps as $item) {
                if($now >= date("Y-m-d H:i:s", strtotime($item->finished_at))) {
                    $attempts[] = Attempt::mapData($item);
                }
            }
        }
        return $this->responseOK(ClassMeeting::mapDetailData(
            $meeting, ['attendees' => $attendees, 'quiz_attempts' => $attempts]
        ));
    }

    public function lessons($id, $meetingId) {
        $class  = ClassModel::where("id", $id)
                    ->where("creator_id", Auth::id())
                    ->first();
        if(!$class) return $this->responseError("Kelas tidak ada");

        $meeting   = ClassMeeting::where("id", $meetingId)
                    ->where("class_id", $id)
                    ->first();
        if(!$meeting) return $this->responseError("Pertemuan tidak ada");

        $data = $meeting->lessons->map(function($item) {
            return ClassMeetingLesson::mapData($item);
        });
        return $this->responseOK($data);
    }

    public function createLesson(Request $request, $id, $meetingId) {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);
        if ($validator->fails()) return $this->responseInvalidInput($validator->errors());
        
        $class  = ClassModel::where("id", $id)
                    ->where("creator_id", Auth::id())
                    ->first();
        if(!$class) return $this->responseError("Kelas tidak ada");

        $meeting   = ClassMeeting::where("id", $meetingId)
                    ->where("class_id", $id)
                    ->first();
        if(!$meeting) return $this->responseError("Pertemuan tidak ada");

        $link = $request->link;
        // if($request->pdf){
        //     $link = $this->uploadPDF($request->pdf);
        // }

        $lesson = new ClassMeetingLesson;
        $lesson->class_meeting_id = $meeting->id;
        $lesson->name       = $request->name;
        $lesson->link       = $link;
        $lesson->youtube    = $request->youtube;
        $lesson->save();
        $lesson = ClassMeetingLesson::find($lesson->id);
        return $this->responseOK(ClassMeetingLesson::mapData($lesson));
    }
    
    public function editLesson(Request $request, $id, $meetingId, $lessonId) {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);
        if ($validator->fails()) return $this->responseInvalidInput($validator->errors());

        $class  = ClassModel::where("id", $id)
                    ->where("creator_id", Auth::id())
                    ->first();
        if(!$class) return $this->responseError("Kelas tidak ada");

        $meeting   = ClassMeeting::where("id", $meetingId)
                    ->where("class_id", $id)
                    ->first();
        if(!$meeting) return $this->responseError("Pertemuan tidak ada");

        $lesson = ClassMeetingLesson::find($lessonId);
        if(!$lesson) return $this->responseError("Materi tidak ada");

        $link = $request->link;
        // if($request->pdf){
        //     $link = $this->uploadPDF($request->pdf);
        // }

        $lesson->name = $request->name;
        $lesson->link = $link;
        $lesson->youtube = $request->youtube;
        $lesson->save();
        $lesson = ClassMeetingLesson::find($lesson->id);
        return $this->responseOK(ClassMeetingLesson::mapData($lesson));
    }

    public function uploadPDF($base64Encode) {
        $file = $base64Encode;
        $base = base64_decode($file);
        $filename = time().".pdf";
        $dir = "storage/file/";
        if(!file_exists(base_path("storage/app/public/file"))) mkdir(base_path("storage/app/public/file"));
        $destinationPath = base_path("storage/app/public/file/" . $filename);
        file_put_contents($destinationPath, $base);
        return $dir . $filename;
    }
    
    public function deleteLesson($id, $meetingId, $lessonId) {
        $class  = ClassModel::where("id", $id)
                    ->where("creator_id", Auth::id())
                    ->first();
        if(!$class) return $this->responseError("Kelas tidak ada");

        $meeting   = ClassMeeting::where("id", $meetingId)
                    ->where("class_id", $id)
                    ->first();
        if(!$meeting) return $this->responseError("Pertemuan tidak ada");

        $lesson = ClassMeetingLesson::find($lessonId);
        if(!$lesson) return $this->responseError("Materi tidak ada");

        $lesson->delete();
        return $this->responseOK("Berhasil dihapus");
    }


    
}
