<?php

namespace App\Http\Controllers\Wordpress;

use App\Http\Resources\Wordpress\UserResource;
use App\Models\Wordpress\PostMetaWordpress;
use App\Models\Wordpress\PostWordpress;
use App\Models\Wordpress\UserMetaWordpress;
use App\Models\Wordpress\UserWordpress;
use App\Traits\CoreData;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use CoreData;

    public function store(Request $request)
    {
        $user = $this->user->where('user_login', $request->username)->orwhere('user_email', $request->email)->count();
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
        if ($request->user_type == 3) {
            $this->post->post_author = $this->user->ID;
            $this->post->post_title = $request->first_name;
            $this->post->post_type = 'estate_agency';
            $this->post->post_name = $request->username;
            $this->post->save();
            $user_meta = new PostMetaWordpress();
            $user_meta->post_id = $this->post->ID;
            $user_meta->meta_key = 'user_meda_id';
            $user_meta->meta_value = $this->user->ID;
            $user_meta->save();
        } elseif ($request->user_type == 2) {
            $this->post->post_author = $this->user->ID;
            $this->post->post_title = $request->first_name;
            $this->post->post_type = 'estate_agent';
            $this->post->post_name = $request->username;
            $this->post->save();
            $user_meta = new PostMetaWordpress();
            $user_meta->post_id = $this->post->ID;
            $user_meta->meta_key = 'user_meda_id';
            $user_meta->meta_value = $this->user->ID;
            $user_meta->save();
        }
        return response(['status' => 1, 'data' => new UserResource($this->user), 'message' => 'successful'], 200);
    }

    public function show(Request $request)
    {
        $user = $this->user->find($request->user_id);
        return response(['status' => 1, 'data' => new UserResource($user), 'message' => 'successful'], 200);
    }

    public function socail_media(Request $request)
    {
        $account = 0;
        if ($request->type == 'facebook') {
            $user_id = $this->user_meta->where('facebook_id', $request->account_id)->first()->user_id;
            if ($user_id) {
                $user = $this->user->find($user_id);
                return response(['status' => 1, 'data' => new UserResource($user), 'message' => 'successful'], 200);
            } else {
                $account = 1;
                $account_type = 'facebook_id';
            }
        } elseif ($request->type == 'google') {
            $user_id = $this->user_meta->where('google_id', $request->account_id)->first()->user_id;
            if ($user_id) {
                $user = $this->user->find($user_id);
                return response(['status' => 1, 'data' => new UserResource($user), 'message' => 'successful'], 200);
            } else {
                $account = 1;
                $account_type = 'google_id';
            }
        } elseif ($request->type == 'apple') {
            $user_id = $this->user_meta->where('apple_id', $request->account_id)->first()->user_id;
            if ($user_id) {
                $user = $this->user->find($user_id);
                return response(['status' => 1, 'data' => new UserResource($user), 'message' => 'successful'], 200);
            } else {
                $account = 1;
                $account_type = 'apple_id';
            }
        }
        if ($account == 1) {
            if (isset($request->email)) {
                $user = $this->user->where('email', $request->email)->get();
                if ($user) {
                    $user_meta = new UserMetaWordpress();
                    $user_meta->user_id = $user->ID;
                    $user_meta->meta_key = $account_type;
                    $user_meta->meta_value = $request->account_id;
                    $user_meta->save();
                    return response(['status' => 1, 'data' => new UserResource($user), 'message' => 'successful'], 200);
                }
            }
            if (isset($request->username)) {
                $user = $this->user->where('user_login', $request->username)->get();
                if ($user) {
                    $user_meta = new UserMetaWordpress();
                    $user_meta->user_id = $user->ID;
                    $user_meta->meta_key = $account_type;
                    $user_meta->meta_value = $request->account_id;
                    $user_meta->save();
                    return response(['status' => 1, 'data' => new UserResource($user), 'message' => 'successful'], 200);
                }
            }
            $this->user->user_login = $request->username;  //(string) The user's login username.
            $this->user->user_nicename = $request->username;  //(string) The user's login username.
            $this->user->display_name = $request->username;  //(string) The user's login username.
            $this->user->user_status = 0;  //(string) The user's login username.
            $this->user->user_pass = "";  //(string) The plain-text user password.
            $this->user->user_email = $request->email;  //(string) The user email address.
            $this->user->save();
            $data_meta = array('user_estate_role' => '0',
                'nickname' => $request->username,
                'first_name' => $request->username,
                $account_type => $request->account_id,
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
    }
}