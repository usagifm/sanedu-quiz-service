<?php

namespace App\Traits;

use Illuminate\Http\Request;

trait BaseResponseTrait {

    // example: 
    // responseOK($varData)
    // example with custome response Code: 
    // responseOK($varData, 200)
    public function responseOK($data, $responseCode = 200) {
        if(is_string($data))
            return response()->json(["message" => $data], $responseCode);
        return response()->json($data, $responseCode);
    }

    // example: 
    // responseError("Error Message")
    // example with custome response Code: 
    // responseError("Error Message", 123)
    public function responseError($message = null, $responseCode = 487) {
        return response()->json(["message" => $message], $responseCode, [], JSON_NUMERIC_CHECK);
    }
    
    // example: 
    // responseNotValidInput($jsonErrorValidation)
    // example with custome response Code: 
    // responseNotValidInput($jsonErrorValidation, 123)
    public function responseInvalidInput($data = null, $responseCode = 488) {
        return response()->json($data, $responseCode, [], JSON_NUMERIC_CHECK);
    }
    
}