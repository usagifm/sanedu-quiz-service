<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\AttemptCorrection;
use App\Models\ClassMeeting;
use App\Models\ClassStudent;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{

    public function getQuizDetail($id)
    {
        $quizCheck = Quiz::where("id", $id)->first();
        if(!$quizCheck) return $this->responseError("Quiz Tidak Terdaftar !");

        $meet = ClassMeeting::where("id", $quizCheck->meeting_id)->first();
        if(!$meet) return $this->responseError("Pertemuan Tidak Terdaftar !");

        $class = ClassStudent::where('student_id', Auth::user()->id)
            ->where('class_id', $meet->class_id)
            ->first();
        if(!$class) return $this->responseError("Anda Belum Mendaftar di kelas ini !");

        return $this->responseOK(Quiz::mapDetailData($quizCheck));
    }

    public function startQuiz($id)
    {
        $quizCheck = Quiz::where("id", $id)->first();

        if(!$quizCheck) return $this->responseError("Quiz Tidak Terdaftar !");

        $meet = ClassMeeting::where("id", $quizCheck->meeting_id)->first();
        if(!$meet) return $this->responseError("Pertemuan Tidak Terdaftar !");

        $meetDate = $meet->date;
        $meetStart = $meet->start_time;
        $meetEnd = $meet->finish_time;

        $class = ClassStudent::where('student_id', Auth::user()->id)
            ->where('class_id', $meet->class_id)
            ->first();

        if(!$class) return $this->responseError("Anda Belum Mendaftar di kelas ini !");

        $ongoingQuizCheck = date("Y-m-d H:i:s");   
        $check = Attempt::where('quiz_id', $id)
            ->where('student_id', Auth::user()->id)
            ->where('started_at', '<', $ongoingQuizCheck)
            ->where('finished_at', '>', $ongoingQuizCheck)
            ->first();

        if($check) return $this->responseOK(Attempt::mapDetailData($check));

        $timeNow = date("H:i:s");
        $dateNow = date("Y-m-d");

        if($dateNow == $meetDate) {
            if($timeNow > $meetStart) {
                if($timeNow < $meetEnd) {
                    $attempt = new Attempt;
                    $attempt->quiz_id = $id;
                    $attempt->student_id = Auth::user()->id;
                    $attempt->started_at = date("Y-m-d H:i:s");
                    
                    $ubah_format = date("Y-m-d H:i:s");
                    $date = date_create($ubah_format);

                    date_add($date, date_interval_create_from_date_string($quizCheck->duration . ' minutes'));
                    $hasil = date_format($date, "Y-m-d H:i:s");


                    $attempt->finished_at = $hasil;
                    $attempt->save();
                    return $this->responseOK(Attempt::mapDetailData($attempt));
                } else if($timeNow > $meetEnd) {
                    return $this->responseError("Waktu Pertemuan Telah Berakhir");
                }
            } else if($timeNow < $meetStart) {
                return $this->responseError("Pertemuan Belum di Mulai !");
            }
        } else if($dateNow != $meetDate) {
            return $this->responseError("Waktu Pertemuan Tidak Cocok !");
        }
    }

    public function finishQuiz($id)
    {
        $quizCheck = Quiz::where("id", $id)->first();
        if(!$quizCheck) return $this->responseError("Quiz Tidak Terdaftar !");

        $meet = ClassMeeting::where("id", $quizCheck->meeting_id)->first();
        if(!$meet) return $this->responseError("Pertemuan Tidak Terdaftar !");

        $class = ClassStudent::where('student_id', Auth::user()->id)
            ->where('class_id', $meet->class_id)
            ->first();
        if(!$class) return $this->responseError("Anda Belum Mendaftar di kelas ini !");

        $now = date("Y-m-d H:i:s");

        $attempt = Attempt::where('quiz_id', $id)
            ->where('student_id', Auth::user()->id)
            ->where('started_at', '<', $now)
            ->first();
        if($attempt->finished_at <= $now) return $this->responseOK(Attempt::mapDetailData($attempt));

        $quizMaxTime = $attempt->finished_at;
        $finishNow = date("Y-m-d H:i:s");

        if($finishNow < $quizMaxTime) {
            $attempt->finished_at = $finishNow;
            $attempt->save();
        } else if($finishNow > $quizMaxTime) {
            $attempt->finished_at = $quizMaxTime;
            $attempt->save();
        }
        return $this->responseOK(Attempt::mapDetailData($attempt));
    }

    public function answerQuestion(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'attempt_id' => 'required',
            'quiz_question_id' => 'required',
        ]);
        if($validator->fails()) return $this->responseInvalidInput($validator->errors());

        $quizCheck = Quiz::where("id", $id)->first();
        if(!$quizCheck) return $this->responseError("Quiz Tidak Terdaftar !");

        $meet = ClassMeeting::where("id", $quizCheck->meeting_id)->first();
        if(!$meet) return $this->responseError("Pertemuan Tidak Terdaftar !");

        $class = ClassStudent::where('student_id', Auth::user()->id)
            ->where('class_id', $meet->class_id)
            ->first();
        if(!$class) return $this->responseError("Anda Belum Mendaftar di kelas ini !");

        $ongoingQuizCheck = date("Y-m-d H:i:s");

        $check = Attempt::where('quiz_id', $id)
            ->where('student_id', Auth::user()->id)
            ->where('started_at', '<', $ongoingQuizCheck)
            ->where('finished_at', '>', $ongoingQuizCheck)
            ->first();
        if(!$check) return $this->responseError("Kuis Telah Selesai !");

        $isAnswered = AttemptCorrection::where('attempt_id', $request->attempt_id)
            ->where('quiz_question_id', $request->quiz_question_id)
            ->first();

        if($isAnswered) return $this->responseOK(AttemptCorrection::mapData($isAnswered));

        $jawaban = new AttemptCorrection;
        $jawaban->attempt_id = $request->attempt_id;
        $jawaban->quiz_question_id = $request->quiz_question_id;
        if($check->id != $request->attempt_id) return $this->responseError("Anda Tidak Dapat Mengubah Jawaban Quiz Lain !");

        $answerCheck = QuizQuestion::where('id', $request->quiz_question_id)->first();
        $jawaban->answer = $request->answer;
        $jawaban->is_corrected = $answerCheck->question_type == 1;
        if($request->answer == $answerCheck->answer)
            $jawaban->is_correct = true;
        else if($request->answer != $answerCheck->answer)
            $jawaban->is_correct = false;
        
        if($request->answer_image) {
            $jawaban->answer_image = $this->uploadImage($request->answer_image);
        }
        
        $jawaban->save();

        return $this->responseOK(AttemptCorrection::mapData($jawaban));
    }

    public function updateQuestion(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'answer' => 'required',
        ]);
        if($validator->fails()) return $this->responseInvalidInput($validator->errors());

        $quizCheck = Quiz::where("id", $id)->first();
        if(!$quizCheck) return $this->responseError("Quiz Tidak Terdaftar !");

        $meet = ClassMeeting::where("id", $quizCheck->meeting_id)->first();
        if(!$meet) return $this->responseError("Pertemuan Tidak Terdaftar !");

        $class = ClassStudent::where('student_id', Auth::user()->id)
            ->where('class_id', $meet->class_id)
            ->first();
        if(!$class) return $this->responseError("Anda Belum Mendaftar di kelas ini !");

        $ongoingQuizCheck = date("Y-m-d H:i:s");

        $check = Attempt::where('quiz_id', $id)
            ->where('student_id', Auth::user()->id)
            ->where('started_at', '<', $ongoingQuizCheck)
            ->where('finished_at', '>', $ongoingQuizCheck)
            ->first();

        if(!$check) return $this->responseError("Kuis Telah Selesai ! ");

        $jawaban = AttemptCorrection::where('id', $request->id)->first();

        if($check->id != $jawaban->attempt_id)
            return $this->responseError("Anda Tidak Dapat Mengubah Jawaban Quiz Lain !");

        if(!$jawaban) return $this->responseError("Jawaban Anda Tidak Ada !");

        $answerCheck = QuizQuestion::where('id', $jawaban->quiz_question_id)->first();
        $jawaban->answer = $request->answer;
        $jawaban->is_corrected = $answerCheck->question_type == 1;
        if($request->answer == $answerCheck->answer)
            $jawaban->is_correct = true;
        else if($request->answer != $answerCheck->answer)
            $jawaban->is_correct = false;
        
        if($request->answer_image) {
            $jawaban->answer_image = $this->uploadImage($request->answer_image);
        }
        $jawaban->save();
        return $this->responseOK(AttemptCorrection::mapData($jawaban));
    }

    public function deleteQuestion(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        if($validator->fails()) return $this->responseInvalidInput($validator->errors());

        $quizCheck = Quiz::where("id", $id)->first();
        if(!$quizCheck) return $this->responseError("Quiz Tidak Terdaftar !");

        $meet = ClassMeeting::where("id", $quizCheck->meeting_id)->first();
        if(!$meet) return $this->responseError("Pertemuan Tidak Terdaftar !");

        $class = ClassStudent::where('student_id', Auth::user()->id)
            ->where('class_id', $meet->class_id)
            ->first();

        if(!$class) return $this->responseError("Anda Belum Mendaftar di kelas ini !");

        $ongoingQuizCheck = date("Y-m-d H:i:s");

        $check = Attempt::where('quiz_id', $id)
            ->where('student_id', Auth::user()->id)
            ->where('started_at', '<', $ongoingQuizCheck)
            ->where('finished_at', '>', $ongoingQuizCheck)
            ->first();
        if(!$check) return $this->responseError("Kuis Telah Selesai !");

        $jawaban = AttemptCorrection::find($request->id);
        if($check->id != $jawaban->attempt_id) return $this->responseError("Anda Tidak Dapat Mengubah Jawaban Quiz Lain !");

        if(!$jawaban) return $this->responseError("Jawaban Anda Tidak Ada !");

        if($jawaban) {
            if($jawaban->delete()) return $this->responseOK("DELETED");
            return $this->responseError("REMOVING FAILED");
        }
    }

    public function attemptDetail($id) {
        $attempt = Attempt::where("id", $id)->where("student_id", Auth::user()->id)->first();
        if(!$attempt) return $this->responseError("Attempt tidak ada");
        return $this->responseOK(Attempt::mapDetailData($attempt));
    }

}
