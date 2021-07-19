<?php

use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
// date_default_timezone_set('Asia/Jakarta');

$router->get('/', function () use ($router) {
    return "Welcome to sanquiz services ! This is instance number !";
});

$router->post('check-username',     'AuthController@username');
$router->post('check-email',        'AuthController@email');
$router->post('register',           'AuthController@register');
$router->post('forgot-password',    'AuthController@forgotPassword');
$router->post('login',              'AuthController@login');

$router->get('general/version',     'GeneralController@version');
$router->get('general/time',        'GeneralController@time');

$router->group(['middleware' => 'auth'], function ($router) {
    $router->post('logout',             'AuthController@logout');

    // $router->get('/profile',            ['uses' => 'ProfileController@getProfileDetail', 'middleware' => 'auth']);
    // $router->post('/profile/edit',      ['uses' => 'ProfileController@editProfile', 'middleware' => 'auth']);



    // ------------------------------------------------------------------
    // TEACHER PREFIXS
    // ------------------------------------------------------------------
    $router->group(['prefix' => 'teacher'], function ($router) {
        // $router->get('/class',              ['uses' => 'Teacher\ClassController@classes', 'middleware' => 'auth']);
        // $router->post('/class/create',      ['uses' => 'Teacher\ClassController@create', 'middleware' => 'auth']);
        // $router->get('/class/delete/{id}',  ['uses' => 'Teacher\ClassController@delete', 'middleware' => 'auth']);
        // $router->post('/class/edit/{id}',   ['uses' => 'Teacher\ClassController@update', 'middleware' => 'auth']);
        // $router->get('/class/{id}',         ['uses' => 'Teacher\ClassController@detail', 'middleware' => 'auth']);
        // $router->get('/class/{id}',         ['uses' => 'Teacher\ClassController@detail', 'middleware' => 'auth']);
        // $router->post('/class/{id}/editclasscode', ['uses' => 'Teacher\ClassController@editClassCode', 'middleware' => 'auth']);

        // $router->group(['prefix' => 'class/{id}/students'], function ($router) {
        //     $router->get('/delete/{studentId}',  ['uses' => 'Teacher\ClassController@deleteStudent', 'middleware' => 'auth']);
        // });


        // $router->group(['prefix' => 'class/{id}/meeting'], function ($router) {
        //     $router->get('/',                    ['uses' => 'Teacher\ClassController@meetings', 'middleware' => 'auth']);
        //     $router->post('/create',             ['uses' => 'Teacher\ClassController@createClassMeeting', 'middleware' => 'auth']);
        //     $router->get('/delete/{meetingId}',  ['uses' => 'Teacher\ClassController@deleteClassMeeting', 'middleware' => 'auth']);
        //     $router->post('/edit/{meetingId}',   ['uses' => 'Teacher\ClassController@editClassMeeting', 'middleware' => 'auth']);
        //     $router->get('/{meetingId}',         ['uses' => 'Teacher\ClassController@detailMeeting', 'middleware' => 'auth']);
        // });

        // $router->group(['prefix' => 'class/{id}/meeting/{meetingId}/lesson'], function ($router) {
        //     $router->get('/',                    ['uses' => 'Teacher\ClassController@lessons', 'middleware' => 'auth']);
        //     $router->post('/create',             ['uses' => 'Teacher\ClassController@createLesson', 'middleware' => 'auth']);
        //     $router->get('/delete/{lessonId}',   ['uses' => 'Teacher\ClassController@deleteLesson', 'middleware' => 'auth']);
        //     $router->post('/edit/{lessonId}',    ['uses' => 'Teacher\ClassController@editLesson', 'middleware' => 'auth']);
        // });

        $router->group(['prefix' => 'quiz'], function ($router) {
            $router->post('/create',                ['uses' => 'Teacher\QuizController@createQuiz', 'middleware' => 'auth']);
            $router->get('/delete/{id}',            ['uses' => 'Teacher\QuizController@deleteQuiz', 'middleware' => 'auth']);
            $router->get('/{id}',                   ['uses' => 'Teacher\QuizController@detailQuiz', 'middleware' => 'auth']);
            $router->post('/{id}/create-question',  ['uses' => 'Teacher\QuizController@createQuestion', 'middleware' => 'auth']);
            $router->get('/{id}/delete-question/{questionId}',      ['uses' => 'Teacher\QuizController@deleteQuestion', 'middleware' => 'auth']);
            $router->post('/{id}/edit-question/{questionId}',       ['uses' => 'Teacher\QuizController@createQuestion', 'middleware' => 'auth']);
            $router->get('/{id}/get-essay-answer/{questionId}',     ['uses' => 'Teacher\QuizController@getEssayAnswer', 'middleware' => 'auth']);
            $router->post('/{id}/correct-essay-answer/{questionId}',['uses' => 'Teacher\QuizController@correctEssayAnswer', 'middleware' => 'auth']);
            $router->get('/{id}/attempt/{attemptId}',               ['uses' => 'Teacher\QuizController@attemptDetail', 'middleware' => 'auth']);
        });
    });
    
    // ------------------------------------------------------------------
    // STUDENT PREFIXS
    // ------------------------------------------------------------------
    $router->group(['prefix' => 'user'], function ($router) {
        $router->get('/classes',                                        ['uses' => 'Student\ClassController@getAssignedClass', 'middleware' => 'auth']);
        $router->post('/search',                                        ['uses' => 'Student\ClassController@searchClass', 'middleware' => 'auth']);
        $router->get('/class/{id}/register',                            ['uses' => 'Student\ClassController@registerClass', 'middleware' => 'auth']);
        $router->get('/class/{id}/resign',                              ['uses' => 'Student\ClassController@resignClass', 'middleware' => 'auth']);
        $router->get('/class/{id}',                                     ['uses' => 'Student\ClassController@detailAssignedClass', 'middleware' => 'auth']);
        $router->get('/class/{id}/meeting/{meetId}',                    ['uses' => 'Student\ClassController@detailMeeting', 'middleware' => 'auth']);
        // $router->get('/class/{id}/meetings',                            ['uses' => 'Student\ClassController@listMeeting', 'middleware' => 'auth']);
        // $router->get('/class/{id}/meeting/{meetId}/lesson/{lessonId}',  ['uses' => 'Student\ClassController@detailLesson', 'middleware' => 'auth']);
        // $router->post('/class/attendlesson/{lessonId}',                 ['uses' => 'Student\ClassController@attendees', 'middleware' => 'auth']);
        $router->get('/quiz/{id}',                                      ['uses' => 'Student\QuizController@getQuizDetail', 'middleware' => 'auth']);
        $router->post('/quiz/{id}/start',                               ['uses' => 'Student\QuizController@startQuiz', 'middleware' => 'auth']);
        $router->post('/quiz/{id}/finish',                              ['uses' => 'Student\QuizController@finishQuiz', 'middleware' => 'auth']);
        $router->post('/quiz/{id}/answer/insert',                       ['uses' => 'Student\QuizController@answerQuestion', 'middleware' => 'auth']);
        $router->post('/quiz/{id}/answer/update',                       ['uses' => 'Student\QuizController@updateQuestion', 'middleware' => 'auth']);
        $router->post('/quiz/{id}/answer/delete',                       ['uses' => 'Student\QuizController@deleteQuestion', 'middleware' => 'auth']);
        $router->get('/attempts/{id}',                                  ['uses' => 'Student\QuizController@attemptDetail', 'middleware' => 'auth']);
    });
});