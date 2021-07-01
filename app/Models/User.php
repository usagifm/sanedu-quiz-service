<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract, JWTSubject {

    use Authenticatable, CanResetPassword;

    protected $table        = 'tbl_users';
    protected $hidden       = [
        'password', 'remember_token',
    ];
    public $timestamps = false;


    protected static function boot() {
        parent::boot();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    
  public function classes(){
        return $this->belongsToMany('App\Models\ClassModel', 'tbl_class_students', 'student_id', 'class_id');
      }


    // $data is Array Data
    // $additionalAttribute is Array Data
    public static function mapData($data, $additionalAttribute = null) {
        $result = [
            'id' => $data->id,
            'role_id' => $data->role_id,
            'name' => $data->name,
            'email' => $data->email,
            'username' => $data->username,
            'birth_date' => $data->birth_date,
            'province_id' => $data->province_id,
            'province_name' => $data->province_name,
            'city_id' => $data->city_id,
            'city_name' => $data->city_name,
            'school' => $data->school,
            'nip' => $data->nip,
            'grade_level' => $data->grade_level,
            'is_teacher' => $data->is_teacher,
            'parent_phone_number' => $data->parent_phone_number,
            'whatsapp' => $data->whatsapp,
            'profile_image' => $data->profile_image != null ? env('APP_STORAGE_URL') . $data->profile_image : null,
        ];
        if($additionalAttribute) {
            $result = array_merge($result, $additionalAttribute);
        }
        return $result;
    }

}
