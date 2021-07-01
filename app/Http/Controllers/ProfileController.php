<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{

    public function getProfileDetail()
    {

        $class = User::where("id", Auth::user()->id)
            ->first();

        if (!$class) {
            return $this->responseError("Data Tidak Ditemukan");
        }

        return $this->responseOK(User::mapData($class));
    }

    public function editProfile(Request $request)
    {

        $id = Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'email' => "email|unique:tbl_users,email,$id",
            'username' => "unique:tbl_users,username,$id",
            'whatsapp' => "unique:tbl_users,whatsapp,$id",
        ]);

        if ($validator->fails()) {
            return $this->responseInvalidInput($validator->errors());
        }

        $data = User::find(Auth::user()->id);
        if (!$data) {
            return $this->responseError("User Tidak Ditemukan");
        }

        if ($request->name) {
            $data->name = $request->name;
        }
        if ($request->email) {
            $data->email = $request->email;
        }
        if ($request->username) {
            $data->username = $request->username;
        }
        if ($request->birth_date) {
            $data->birth_date = $request->birth_date;
        }
        if ($request->province_id) {
            $data->province_id = $request->province_id;
        }
        if ($request->nip) {
            $data->nip = $request->nip;
        }
        if ($request->province_name) {
            $data->province_name = $request->province_name;
        }
        if ($request->city_id) {
            $data->city_id = $request->city_id;
        }
        if ($request->city_name) {
            $data->city_name = $request->city_name;
        }
        if ($request->school) {
            $data->school = $request->school;
        }
        if ($request->grade_level) {
            $data->grade_level = $request->grade_level;
        }
        if ($request->whatsapp) {
            $data->whatsapp = $request->whatsapp;
        }
        if ($request->whatsapp) {
            $data->whatsapp = $request->whatsapp;
        }
        if ($request->parent_phone_number) {
            $data->parent_phone_number = $request->parent_phone_number;
        }
        if($request->profile_image) {
            $data->profile_image = $this->uploadImage($request->profile_image);
        }

        if ($request->old_password) {
            if ($request->password) {
                if (Hash::check($request->old_password, $data->password)) {
                    if ($request->password) {
                        if ($request->re_password) {
                            if ($request->password == $request->re_password) {
                                $hashed = Hash::make($request->password);
                                $data->password = $hashed;
                            } else {
                                return $this->responseError("Password baru anda tidak sama !");
                            }
                        } else if (!$request->re_password) {
                            return $this->responseError("Masukan ulang password baru anda !");
                        }
                    } else if (!$request->password) {
                        return $this->responseError("Masukan password baru anda !");
                    }
                } else if (!$request->password) {
                    return $this->responseError("Password anda tidak cocok !");
                }
            } else {
                return $this->responseError("Masukan Password Baru Anda !");
            }
        }

        $data->save();
        return $this->responseOK(User::mapData($data));
    }

}
