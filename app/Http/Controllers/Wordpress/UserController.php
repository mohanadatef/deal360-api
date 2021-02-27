<?php

namespace App\Http\Controllers\Wordpress;

use App\Http\Resources\Wordpress\UserResource;
use App\Models\Wordpress\UserMetaWordpress;
use App\Models\Wordpress\UserWordpress;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $user, $user_meta;

    public function __construct(UserWordpress $user, UserMetaWordpress $user_meta)
    {
        $this->user = $user;
        $this->user_meta = $user_meta;
    }

    public function store(Request $request)
    {
        $user = $this->user->where('user_login',$request->username)->orwhere('user_email',$request->email)->count();
        if ($user != 0) {
            return response(['status' => 0, 'data' => array(), 'message' => "UserName Exist OR Email Exist"], 422);
        }
        $this->user->user_login = $request->username;  //(string) The user's login username.
        $this->user->user_nicename = $request->username;  //(string) The user's login username.
        $this->user->display_name = $request->username;  //(string) The user's login username.
        $this->user->user_status = 0;  //(string) The user's login username.
        $this->user->user_pass = Hash::make($request->password);  //(string) The plain-text user password.
        $this->user->user_email = $request->email;  //(string) The user email address.
        $this->user->save();
        $data_meta = array('user_estate_role' => $request->user_type,
            'nickname' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone_number,
        );
        foreach ($data_meta as $key => $value) {
            $user_meta = new UserMetaWordpress();
            $user_meta->user_id = $this->user->ID;
            $user_meta->meta_key = $key;
            $user_meta->meta_value = $value;
            $user_meta->save();
        }
        return response(['status' => 1, 'data' => new UserResource($this->user), 'message' => 'successful'], 200);
    }

    public function show(Request $request)
    {
        $user = $this->user->find($request->user_id);
        return response(['status' => 1, 'data' => new UserResource($user), 'message' => 'successful'], 200);
    }
}