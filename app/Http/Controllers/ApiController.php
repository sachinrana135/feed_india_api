<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\AppUser;
use App\Marker;
use App\PushMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use YoHang88\LetterAvatar\LetterAvatar;

class ApiController extends Controller {

    /**
     * ApiController constructor.
     */
    public function __construct() {

    }


    public function saveUser(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $user_data = json_decode($request->get("user"));

            $marker = new Marker();

            $marker->lat = $user_data->lat;
            $marker->lng = $user_data->lng;
            $marker_id = $marker->save();

            $appUser = new AppUser();

            $appUser->firebase_id = $user_data->firebaseId;
            $appUser->name = $user_data->name;
            $appUser->mobile = $user_data->mobile;
            $appUser->marker_id = $marker_id;
            $appUser->save();

            $response->userId  = (String) $appUser->id;
            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }


}
