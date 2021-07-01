<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SetGeneral;

class GeneralController extends Controller
{
    public function version()
    {
        $set = SetGeneral::first();
        return $this->responseOK([
            "android_version" => $set->android_version ?? "0",
            "android_version_forced" => $set->android_version_forced == 0 ? false : true,
            "android_download_link" => $set->android_download_link ?? "",
        ]);
    }
}
