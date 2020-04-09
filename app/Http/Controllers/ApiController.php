<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\AppUser;
use App\Marker;
use App\Group;
use App\GroupUser;
use App\DonorItem;
use App\NeedierItem;
use App\NeedierStatusType;
use App\NeedierItemComment;
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
            $user_data = json_decode($request->getContent());

            DB::beginTransaction();

            $marker = new Marker();
            $marker->lat = $user_data->lat;
            $marker->lng = $user_data->lng;
            $marker->location_address = $user_data->location_address;
            $marker->save();

            $appUser = new AppUser();
            $appUser->firebase_id = $user_data->firebaseId;
            $appUser->name = $user_data->name;
            $appUser->mobile = $user_data->mobile;
            $appUser->marker_id = $marker->id;
            $appUser->user_type = "DNR";
            $appUser->active = $user_data->status;
            $appUser->save();

            $donorItem = new DonorItem();
            $donorItem->user_id = $appUser->id;
            $donorItem->donate_items = $user_data->donate_items;
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

            $user_data = json_decode($request->getContent());

            DB::beginTransaction();


            $sql = DB::table("users")
                    ->leftJoin('group_users', 'users.id', '=', 'group_users.user_id')
                    ->select('users.id');

            $sql->where("users.mobile", $user_data->mobile);
            $sql->where("group_users.group_id", $user_data->group_id);

            $user_exist = $sql->get()->first();

            if (!is_null($user_exist)) {
                 throw new \Exception("Needier already exist!");
            }

            $marker = new Marker();
            $marker->lat = $user_data->lat;
            $marker->lng = $user_data->lng;
            $marker->location_address = $user_data->location_address;
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

            $response->userId  = (String) $appUser->id;
            $apiResponse->setResponse($response);
            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            DB::rollBack();
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }


    public function saveMember(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $response = app()->make('stdClass');

            $user_data = json_decode($request->getContent());

            DB::beginTransaction();

            $marker = new Marker();
            $marker->lat = $user_data->lat;
            $marker->lng = $user_data->lng;
            $marker->location_address = $user_data->location_address;
            $marker->save();

            $appUser = new AppUser();
            $appUser->name = $user_data->name;
            $appUser->mobile = $user_data->mobile;
            $appUser->marker_id = $marker->id;
            $appUser->user_type = "MBR";
            $appUser->save();

            $group = Group::where('code', $user_data->groupCode)->first();

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

    public function saveComment(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $data = json_decode($request->getContent());

            $comment = new NeedierItemComment();
            $comment->needier_items_id = $data->needier_item_id;
            $comment->member_id = $data->member_id;
            $comment->comments = $data->comment;
            $comment->save();

            $apiResponse->setResponse("Success");
            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
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

            $marker = Marker::find($user->marker_id);
            $response->lat = $marker->lat;
            $response->lng = $marker->lng;
            $response->location_address = $marker->location_address;

            if($user->user_type == "DNR") {
                $donate_items = DonorItem::where('user_id', $user->id)->first();
                $response->donateItems = $donate_items->donate_items;
                $response->donorVisibility = $donate_items->status == 0 ? false: true;
            }else if($user->user_type == "NDR") {
                $need_items = NeedierItem::where('user_id', $user->id)->first();
                $response->needierItemId = (String) $need_items->id;
                $response->needItems = $need_items->items_need;
            }else if($user->user_type == "MBR") {
                $response->isAdmin = $user->is_admin == 0 ? false : true ;

                $sql = DB::table("group_users")
                ->leftJoin('groups', 'group_users.group_id', '=', 'groups.id')
                ->select('groups.id as groupId','groups.code as groupCode', 'groups.name as groupName');
                $sql->where("group_users.user_id", $userId);

                $group = $sql->get()->first();
                $response->groupId = $group->groupId;
                $response->groupName = $group->groupName;
                $response->groupCode = $group->groupCode;
            }

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

            $marker = Marker::find($user->marker_id);
            $response->lat = $marker->lat;
            $response->lng = $marker->lng;
            $response->location_address = $marker->location_address;

            if($user->user_type == "DNR") {
                $donate_items = DonorItem::where('user_id', $user->id)->first();
                $response->donateItems = $donate_items->donate_items;
                 $response->donorVisibility = $donate_items->status == 0 ? false: true;
            }else if($user->user_type == "NDR") {
                $need_items = NeedierItem::where('user_id', $user->id)->first();
                $response->needierItemId = (String) $need_items->id;
                $response->needItems = $need_items->items_need;
            }else if($user->user_type == "MBR") {
                $response->isAdmin = $user->is_admin == 0 ? false : true ;
                $sql = DB::table("group_users")
                ->leftJoin('groups', 'group_users.group_id', '=', 'groups.id')
                ->select('groups.id as groupId','groups.code as groupCode', 'groups.name as groupName');
                $sql->where("group_users.user_id", $user->id);

                $group = $sql->get()->first();
                $response->groupId = $group->groupId;
                $response->groupName = $group->groupName;
                $response->groupCode = $group->groupCode;
            }

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

            $group_data = json_decode($request->getContent());

            DB::beginTransaction();

            $marker = new Marker();
            $marker->lat = $group_data->lat;
            $marker->lng = $group_data->lng;
            $marker->location_address = $group_data->location_address;
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
            $response->groupId  = (String) $group->id;
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


    public function getGroupNeedierItems(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $response = array();

            $status_id = (int) $request->get("status");
            $group_id = (int) $request->get("group_id");
            $page =  $request->get("page");
            $pageLoad = $request->get("page_load");

            $sql = DB::table("needier_items")
                    ->leftJoin('users', 'users.id', '=', 'needier_items.user_id')
                    ->leftJoin('group_users', 'users.id', '=', 'group_users.user_id')
                    ->leftJoin('markers', 'users.marker_id', '=', 'markers.id')
                    ->leftJoin('item_status', 'needier_items.status_id', '=', 'item_status.id')
                    ->select('needier_items.id as needier_item_id','needier_items.user_id', 'users.name', 'users.mobile','markers.lat','markers.lng','markers.location_address','needier_items.items_need', 'item_status.name as status');
            $sql->where("users.active", 1);
            $sql->where("users.user_type", 'NDR');
            $sql->where("group_users.group_id", $group_id);
            $sql->where("needier_items.status_id", $status_id);

            $sql->orderBy('users.id', 'desc');
            $sql->paginate($pageLoad, ['*'], 'page', $page);

            $users = $sql->get();

            foreach ($users as $user) {
                $userObject = app()->make('stdClass');
                $userObject->needier_item_id = (String) $user->needier_item_id;
                $userObject->name =  $user->name;
                $userObject->mobile =  $user->mobile;
                $userObject->address =  $user->location_address;
                $userObject->items_need =  $user->items_need;
                $userObject->status =  $user->status;

                $response[] =  $userObject;
            }

            $apiResponse->setResponse($response);
            return $apiResponse->outputResponse($apiResponse);
        } catch (\Exception $e) {
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }

    public function getNeedier(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $needier_item_id = (int) $request->get("needier_item_id");

            $sql = DB::table("needier_items")
                    ->leftJoin('users', 'users.id', '=', 'needier_items.user_id')
                    ->leftJoin('markers', 'users.marker_id', '=', 'markers.id')
                    ->leftJoin('item_status', 'needier_items.status_id', '=', 'item_status.id')
                    ->select('needier_items.id as needier_item_id','needier_items.user_id', 'users.name', 'users.mobile','markers.lat','markers.lng','markers.location_address as address','needier_items.items_need', 'item_status.name as status');
            $sql->where("needier_items.id", $needier_item_id);

            $needier = $sql->get()->first();

            $apiResponse->setResponse($needier);
            return $apiResponse->outputResponse($apiResponse);
        } catch (\Exception $e) {
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }


    public function getGroupMember(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $response = array();

            $group_id = (int) $request->get("group_id");
            $page = (int) $request->get("page");
            $pageLoad = (int) $request->get("page_load");

            $sql = DB::table("users")
                    ->leftJoin('group_users', 'users.id', '=', 'group_users.user_id')
                    ->leftJoin('markers', 'users.marker_id', '=', 'markers.id')
                    ->select('users.name', 'users.mobile','markers.lat','markers.lng','users.is_admin as isAdmin','markers.location_address as location_address');
            $sql->where("users.active", 1);
            $sql->where("users.user_type", 'MBR');
            $sql->where("group_users.group_id", $group_id);

            $sql->orderBy('users.id', 'desc');
            $sql->paginate($pageLoad, ['*'], 'page', $page);
            $users = $sql->get() ;


            foreach ($users as $user) {
                $userObject = app()->make('stdClass');
                $userObject->name =  $user->name;
                $userObject->mobile =  $user->mobile;
                $userObject->lat =  $user->lat;
                $userObject->lng =  $user->lng;
                $userObject->location_address =  $user->location_address;
                $userObject->isAdmin = $user->isAdmin == 0 ? false : true ;
                $response[] =  $userObject;
            }

            $apiResponse->setResponse($response);
            return $apiResponse->outputResponse($apiResponse);
        } catch (\Exception $e) {
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }

    public function getNeedierItemStatusTypes(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $result = NeedierStatusType::all();
            $apiResponse->setResponse($result);
            return $apiResponse->outputResponse($apiResponse);
        } catch (\Exception $e) {
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }

    public function updateNeedierItemStatus(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $data = json_decode($request->getContent());

            DB::beginTransaction();

            $item = NeedierItem::find($data->needier_item_id);
            if ($item == null) {
                throw new \Exception("Item not found");
            }
            $item->status_id = $data->status_id;
            $item->save();

            $member = AppUser::find($data->member_id);

            if ($member == null) {
                throw new \Exception("Member not found");
            }

            $status = NeedierStatusType::find($data->status_id);

            $needierComment = new NeedierItemComment();
            $needierComment->needier_items_id = $item->id;
            $needierComment->member_id = $member->id;
            $needierComment->comments = $member->name." has changed the status to ". $status->name;
            $needierComment->save();

            DB::commit();

            $apiResponse->setResponse("Success");

            return $apiResponse->outputResponse($apiResponse);
        } catch (\Exception $e) {
            DB::rollBack();
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }

    public function getComments(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $response = array();
            $page = (int) $request->get("page");
            $sql = DB::table("needier_items_comments")
                    ->leftJoin('users', 'users.id', '=', 'needier_items_comments.member_id')
                    ->select('users.name', 'needier_items_comments.comments','needier_items_comments.created_at');
            $sql->where("needier_items_comments.needier_items_id", $request->get("needier_item_id"));

            $sql->orderBy('needier_items_comments.created_at', 'desc');
            $sql->paginate(10, ['*'], 'page', $page);
            $comments = $sql->get();

            foreach ($comments as $comment) {
                $commentObject = app()->make('stdClass');
                $commentObject->member_name = $comment->name;
                $commentObject->comment = $comment->comments;
                $commentObject->dateAdded = Carbon::parse($comment->created_at)->diffForHumans();
                $response[] =  $commentObject;
            }

            $apiResponse->setResponse($response);
            return $apiResponse->outputResponse($apiResponse);
        } catch (\Exception $e) {
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }

    public function getNearByUsers(Request $request) {
        $apiResponse = new ApiResponse();
        try {

            $lat = $request->get("lat");
            $lng = $request->get("lng");
            $distance = (int) $request->get("distance");
            $userType =  $request->get("user_type");
            $group_id =  $request->get("group_id");

            $sql = DB::table('users')
            ->leftJoin('markers', 'users.marker_id', '=', 'markers.id')
            ->leftJoin('group_users', 'users.id', '=', 'group_users.user_id')
            ->select('name', 'mobile','lat', 'lng','user_type', DB::raw(sprintf(
                'ROUND(6371 * acos(cos(radians(%1$.7f)) * cos(radians(lat)) * cos(radians(lng) - radians(%2$.7f)) + sin(radians(%1$.7f)) * sin(radians(lat))),2) AS distance',
                $lat,
                $lng
            )));

            $sql->where('active', 1);
            $sql->having('distance', '<', $distance);
            $sql->orderBy('distance', 'asc');

            $groupUsers = clone $sql;

            $groupUsers->where('group_users.group_id', $group_id);
            $groupUsers->where(function($result) {
             $result->where("user_type" , "MBR")
               ->orWhere('user_type' , "NDR");
             });

             $donor = clone $sql;
             $donor->where('user_type', "DNR");

            $users = $groupUsers
                    ->union($donor)
                    ->get();


            $apiResponse->setResponse($users);
            return $apiResponse->outputResponse($apiResponse);
        } catch (\Exception $e) {
            $apiResponse->error->setMessage($e->getMessage());
            return $apiResponse->outputResponse($apiResponse);
        }
    }

    public function updateDonor(Request $request) {
        $apiResponse = new ApiResponse();
        try {
            $user_data = json_decode($request->getContent());

            DB::beginTransaction();

            $donorItem = DonorItem::where('user_id', $user_data->userId)->first();
            $donorItem->donate_items = $user_data->donate_items;
            $donorItem->status = $user_data->status;
            $donorItem->save();

            DB::commit();

            $apiResponse->setResponse("Success");
            return $apiResponse->outputResponse($apiResponse);

        } catch (\Exception $e) {
            DB::rollBack();
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
