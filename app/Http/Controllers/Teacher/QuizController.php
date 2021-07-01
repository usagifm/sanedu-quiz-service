<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassMeeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassModel;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    
    public function createQuiz(Request $request) {
        $validator = Validator::make($request->all(), [
            'duration'  => 'required|numeric|min:1'
        ]);
        if ($validator->fails()) return $this->responseInvalidInput($validator->errors());

        $meeting = ClassMeeting::find($request->meeting_id);
        if(!$meeting) return $this->responseError("Pertemuan tidak ada");

        $class = ClassModel::where("id", $meeting->class_id)
                    ->where("creator_id", Auth::id())
                    ->first();
        if(!$class) return $this->responseError("Kelas tidak ada");

        $quiz = Quiz::where("meeting_id", $meeting->id)->first();
        if($quiz) return $this->responseOK(Quiz::mapData($quiz));

        $quiz = new Quiz;
        $quiz->creator_id = Auth::id();
        $quiz->meeting_id = $meeting->id;
        $quiz->name = $request->name ?? "Kuis " . $class->name . " - " . $meeting->name;
        $quiz->duration = $request->duration;
        if(!$quiz->save()) return $this->responseError("Gagal membuat kuis");

        return $this->responseOK(Quiz::mapData($quiz));
    }

    public function deleteQuiz($id) {
        $quiz = Quiz::find($id);
        if(!$quiz) return $this->responseError("Quiz tidak ada");

        if($this->notMyQuiz($quiz->meeting_id)) 
            return $this->notMyQuiz($quiz->meeting_id);

        $quiz->delete();

        return $this->responseOK("Berhasil menghapus");
    }
    
    public function detailQuiz($id) {
        $quiz = Quiz::find($id);
        if(!$quiz) return $this->responseError("Quiz tidak ada");

        if($this->notMyQuiz($quiz->meeting_id)) 
            return $this->notMyQuiz($quiz->meeting_id);

        return $this->responseOK(Quiz::mapDetailData($quiz));
    }
    
    public function createQuestion(Request $request, $id, $questionId = null) {
        $quiz = Quiz::find($id);
        if(!$quiz) return $this->responseError("Quiz tidak ada");
        
        if($this->notMyQuiz($quiz->meeting_id)) 
        return $this->notMyQuiz($quiz->meeting_id);
        
        if($request->question_type != 1 && $request->question_type != 2)
            return $this->responseError("Type soal salah");
        
        $question = $questionId ? QuizQuestion::find($questionId) : new QuizQuestion;
        $question->quiz_id = $id;
        $question->question_type = $request->question_type;
        $question->question = $request->question;
        $question->a = $request->a;
        $question->b = $request->b;
        $question->c = $request->c;
        $question->d = $request->d;
        $question->e = $request->e;
        $question->answer = $request->answer;
        $question->essay_answer = $request->essay_answer;
        if($request->question_image) {
            $question->question_image = $this->uploadImage($request->question_image);
        }
        $question->save();
        return $this->responseOK(QuizQuestion::mapData($question));
    }

    public function deleteQuestion($id, $questionId) {
        $question = QuizQuestion::find($questionId);
        if(!$question) return $this->responseError("Question tidak ada");

        $quiz = Quiz::find($id);
        if(!$quiz) return $this->responseError("Quiz tidak ada");

        if($this->notMyQuiz($quiz->meeting_id)) 
            return $this->notMyQuiz($quiz->meeting_id);

        $question->delete();
        return $this->responseOK("Berhasil menghapus");
    }

    public function notMyQuiz($meetingId) {
        $meeting   = ClassMeeting::where("id", $meetingId)->first();
        if(!$meeting) return $this->responseError("Pertemuan tidak ada");

        $class  = ClassModel::where("id", $meeting->class_id)
                    ->where("creator_id", Auth::id())
                    ->first();
        if(!$class) return $this->responseError("Kelas tidak ada");
        return null;
    }

}
