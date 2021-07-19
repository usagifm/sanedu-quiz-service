<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    
    public function login(Request $request) {
        $loginWithEmail = app('auth')->attempt(['email' => $request->username, 'password' => $request->password]);
        $loginWithUsername = app('auth')->attempt(['username' => $request->username, 'password' => $request->password]);
        if ($loginWithEmail) {
            $token = $loginWithEmail;
        }
        else if($loginWithUsername) {
            $token = $loginWithUsername;
        }
        else {
            return $this->responseError("Maaf user tidak ditemukan.");
        }
        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'required',
            'email'                 => 'required|email|unique:tbl_users,email',
            'username'              => 'required|alpha_num|unique:tbl_users,username',
            'whatsapp'              => 'nullable|numeric|unique:tbl_users,whatsapp',
            'parent_phone_number'   => 'nullable|numeric',
            'grade_level'           => 'nullable|numeric|between:1,12',
            'is_teacher'            => 'nullable|boolean',
            'password'              => 'required|min:6',
        ]);
        if ($validator->fails()) return $this->responseInvalidInput($validator->errors());

        if($request->whatsapp[0] == "0") $request->whatsapp = substr($request->whatsapp, 1);
        if(substr($request->whatsapp, 0, 3) == "620") {
            $str1 = substr($request->whatsapp, 0, 2);
            $str2 = substr($request->whatsapp, 3);
            $request->whatsapp = $str1 . $str2;
        };

        $user = new User;
        $user->role_id = 2;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->password = app('hash')->make($request->password);
        $user->birth_date = $request->birth_date;
        $user->province_id = $request->province_id;
        $user->province_name = $request->province_name;
        $user->city_id = $request->city_id;
        $user->city_name = $request->city_name;
        $user->school = $request->school;
        $user->nip = $request->nip;
        $user->grade_level = $request->grade_level;
        $user->is_teacher = $request->is_teacher;
        $user->whatsapp = $request->whatsapp;
        $user->parent_phone_number = $request->parent_phone_number;
        if($request->profile_image) {
            $user->profile_image = $this->uploadImage($request->profile_image);
        }
        $user->save();
        return $this->responseOK(User::mapData($user));
    }

    public function forgotPassword(Request $request) {
        $user = User::where("email", $request->email)->first();
        if($user) {
            $user->password = app('hash')->make(123456);
            return $this->responseOK("Sementara, password diganti jadi 123456");
        }
        return $this->responseError("Email belum terdaftar");
    }

    public function username(Request $request) {
        if(User::where('username', $request->username)->first())
            return $this->responseError("Username sudah digunakan");
        return $this->responseOK("Username tersedia");
    }
    
    public function email(Request $request) {
        if(User::where('email', $request->email)->first())
            return $this->responseError("Email sudah digunakan");
        return $this->responseOK("Email tersedia");
    }

    // public function refresh() {
    //     return $this->respondWithToken(app('auth')->refresh());
    // }

    protected function respondWithToken($token) {
        $token = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => app('auth')->factory()->getTTL() * 10000
        ];
        $data = User::mapData(app('auth')->user(), $token);
        return $this->responseOK($data);
    }

    public function test() {
        return 0;
    }

    public function logout() {
        app('auth')->logout();
        JWTAuth::invalidate(JWTAuth::getToken());
        return $this->responseOK("OK");
    }
}