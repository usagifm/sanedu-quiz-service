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
    return $router->app->version();
});

$router->get('general/version',     'GeneralController@version');

$router->group(['middleware' => 'auth'], function ($router) {

    // ------------------------------------------------------------------
    // TEACHER PREFIXS
    // ------------------------------------------------------------------
    $router->group(['prefix' => 'teacher'], function ($router) {

        $router->group(['prefix' => 'quiz'], function ($router) {
            $router->post('/create',                ['uses' => 'Teacher\QuizController@createQuiz', 'middleware' => 'auth']);
            $router->get('/delete/{id}',            ['uses' => 'Teacher\QuizController@deleteQuiz', 'middleware' => 'auth']);
            $router->get('/{id}',                   ['uses' => 'Teacher\QuizController@detailQuiz', 'middleware' => 'auth']);
            $router->post('/{id}/create-question',  ['uses' => 'Teacher\QuizController@createQuestion', 'middleware' => 'auth']);
            $router->get('/{id}/delete-question/{questionId}',  ['uses' => 'Teacher\QuizController@deleteQuestion', 'middleware' => 'auth']);
            $router->post('/{id}/edit-question/{questionId}',   ['uses' => 'Teacher\QuizController@createQuestion', 'middleware' => 'auth']);
        });
    });
    
    // ------------------------------------------------------------------
    // STUDENT PREFIXS
    // ------------------------------------------------------------------
    $router->group(['prefix' => 'user'], function ($router) {
        $router->get('/quiz/{id}',                                      ['uses' => 'Student\QuizController@getQuizDetail', 'middleware' => 'auth']);
        $router->post('/quiz/{id}/start',                               ['uses' => 'Student\QuizController@startQuiz', 'middleware' => 'auth']);
        $router->post('/quiz/{id}/finish',                              ['uses' => 'Student\QuizController@finishQuiz', 'middleware' => 'auth']);
        $router->post('/quiz/{id}/answer/insert',                       ['uses' => 'Student\QuizController@answerQuestion', 'middleware' => 'auth']);
        $router->post('/quiz/{id}/answer/update',                       ['uses' => 'Student\QuizController@updateQuestion', 'middleware' => 'auth']);
        $router->post('/quiz/{id}/answer/delete',                       ['uses' => 'Student\QuizController@deleteQuestion', 'middleware' => 'auth']);
        $router->get('/attempts/{id}',                                  ['uses' => 'Student\QuizController@attemptDetail', 'middleware' => 'auth']);
    });
});