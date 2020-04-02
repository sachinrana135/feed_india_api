<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\AppUser;
use App\Marker;
use App\Group;
use App\GroupUser;
use App\DonorItem;
use App\NeedierItem;
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

    public function saveDonor(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');
            $user_data = json_decode($request->get("user"));

            DB::beginTransaction();

            $marker = new Marker();
            $marker->lat = $user_data->lat;
            $marker->lng = $user_data->lng;
            $marker->save();

            $appUser = new AppUser();
            $appUser->firebase_id = $user_data->firebaseId;
            $appUser->name = $user_data->name;
            $appUser->mobile = $user_data->mobile;
            $appUser->marker_id = $marker->id;
            $appUser->user_type = "DNR";
            $appUser->save();

            $donorItem = new DonorItem();
            $donorItem->user_id = $appUser->id;
            $donorItem->donate_items = $user_data->donate_items;
            $donorItem->status = $user_data->status;
            $donorItem->save();

            DB::commit();

            $response->userId  = (String) $appUser->id;
            $apiResponse->setResponse($response);
            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            DB::rollBack();
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }


    public function saveNeedier(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $user_data = json_decode($request->get("user"));

            DB::beginTransaction();

            $marker = new Marker();
            $marker->lat = $user_data->lat;
            $marker->lng = $user_data->lng;
            $marker->save();

            $appUser = new AppUser();
            $appUser->name = $user_data->name;
            $appUser->mobile = $user_data->mobile;
            $appUser->marker_id = $marker->id;
            $appUser->user_type = "NDR";
            $appUser->save();

            $groupUser = new GroupUser();
            $groupUser->group_id = $user_data->group_id;
            $groupUser->user_id = $appUser->id;
            $groupUser->save();

            $needierItem = new NeedierItem();
            $needierItem->user_id = $appUser->id;
            $needierItem->items_need = $user_data->need_items;
            $needierItem->save();

            DB::commit();

            $apiResponse->setResponse($response);
            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            DB::rollBack();
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }


    public function getUserById(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $userId = $request->get("userId");

            $user = AppUser::where('id', $userId)
                    ->where('active', 1)
                    ->first();

            if ($user == null) {
                throw new \Exception("User not found");
            }

            $response->id = (string) $user->id;
            $response->firebaseId = $user->firebase_id;
            $response->name = $user->name;
            $response->mobile = $user->mobile;
            $response->userType = $user->user_type;
            $response->isAdmin = $user->is_admin == 0 ? false : true ;

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);
        } catch (\Exception $e) {
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }

    public function getUserByMobile(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $mobile = $request->get("mobile");

            $user = AppUser::where('mobile', $mobile)
                    ->where('active', 1)
                    ->first();

            if ($user == null) {
                throw new \Exception("User not found");
            }

            $response->id = (string) $user->id;
            $response->firebaseId = $user->firebase_id;
            $response->name = $user->name;
            $response->mobile = $user->mobile;
            $response->userType = $user->user_type;
            $response->isAdmin = $user->is_admin == 0 ? false : true ;

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);
        } catch (\Exception $e) {
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }


    public function mapFcmIdToUser(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $userId = $request->get("userId");

            $user = AppUser::find($userId);

            if ($user == null) {
                throw new \Exception("User not found");
            }

            $user->fcmId = $request->get("fcmId");
            $user->save();

            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);
        } catch (\Exception $e) {
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }


    public function saveGroup(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $group_data = json_decode($request->get("group"));

            DB::beginTransaction();

            $marker = new Marker();
            $marker->lat = $group_data->lat;
            $marker->lng = $group_data->lng;
            $marker->save();

            $appUser = new AppUser();
            $appUser->firebase_id = $group_data->firebaseId;
            $appUser->name = $group_data->admin_name;
            $appUser->mobile = $group_data->mobile;
            $appUser->marker_id = $marker->id;
            $appUser->user_type = "MBR";
            $appUser->is_admin = 1;
            $appUser->save();

            $group = new Group();
            $group->code  = $this->random_num(6);
            $group->name  = $group_data->group_name;
            $group->mobile = $group_data->mobile;
            $group->marker_id = $marker->id;
            $group->reg_no = $group_data->reg_no;
            $group->address = $group_data->address;
            $group->save();

            $groupUser = new GroupUser();
            $groupUser->group_id = $group->id;
            $groupUser->user_id = $appUser->id;
            $groupUser->save();

            DB::commit();

            $response->userId  = (String) $appUser->id;
            $apiResponse->setResponse($response);

            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            DB::rollBack();
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }

    public function getNearByGroups(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $lat = $request->get("lat");
            $lng = $request->get("lng");
            $distance = (int) $request->get("distance");

            $groups = DB::table('groups')
            ->leftJoin('markers', 'groups.marker_id', '=', 'markers.id')
            ->select('name', 'mobile','address','lat', 'lng', DB::raw(sprintf(
                '(6371 * acos(cos(radians(%1$.7f)) * cos(radians(lat)) * cos(radians(lng) - radians(%2$.7f)) + sin(radians(%1$.7f)) * sin(radians(lat)))) AS distance',
                $lat,
                $lng
            )))
            ->having('distance', '<', 50)
            ->orderBy('distance', 'asc')
            ->get();

            $apiResponse->setResponse($groups);
            return $apiResponse->outputResponse($apiResponse);
        } catch (\Exception $e) {
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }


    public function getGroupNeedier(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $status_id = (int) $request->get("status");
            $group_id = (int) $request->get("group_id");
            $page = (int) $request->get("page");

            $sql = DB::table("users")
                    ->leftJoin('needier_items', 'users.id', '=', 'needier_items.user_id')
                    ->leftJoin('group_users', 'users.id', '=', 'group_users.user_id')
                    ->select('users.name', 'users.mobile','needier_items.items_need');
            $sql->where("users.active", 1);
            $sql->where("group_users.group_id", $group_id);
            $sql->where("needier_items.status_id", $status_id);

            $sql->orderBy('users.id', 'desc');
            $sql->paginate(10, ['*'], 'page', $page);

            $users = $sql->get();

            $apiResponse->setResponse($users);
            return $apiResponse->outputResponse($apiResponse);
        } catch (\Exception $e) {
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }


    public function random_num($size) {
        $alpha_key = '';
        $keys = range('A', 'Z');

        for ($i = 0; $i < 2; $i++) {
            $alpha_key .= $keys[array_rand($keys)];
        }

        $length = $size - 2;

        $key = '';
        $keys = range(0, 9);

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $alpha_key . $key;
}


}
