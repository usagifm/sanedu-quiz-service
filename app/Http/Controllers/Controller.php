<?php

namespace App\Http\Controllers;

use App\Traits\BaseResponseTrait;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use BaseResponseTrait;

    public function uploadImage($file) {
    
        $this->cloudinary = Configuration::instance();
        $this->cloudinary->cloud->cloudName = 'douzspxoy';
        $this->cloudinary->cloud->apiKey = '363893891244229';
        $this->cloudinary->cloud->apiSecret = 'R7RAOvXUyvG78tAEMMegjnQHiLs';
        $this->cloudinary->url->secure = true; 
        $data = $file;
        $cloudder = (new UploadApi())->upload($data);

        $file_url = $cloudder["url"];
        return $file_url;
    }
}
