<?php

namespace App\Http\Controllers\Wordpress;

use App\Http\Resources\Wordpress\ReviewResource;
use App\Models\Wordpress\CommentMetaWordpress;
use App\Models\Wordpress\CommentWordpress;
use App\Models\Wordpress\UserWordpress;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReviewController extends Controller
{
    protected $user, $comment, $comment_meta;

    public function __construct(UserWordpress $user, CommentWordpress $comment, CommentMetaWordpress $comment_meta)
    {
        $this->user = $user;
        $this->comment = $comment;
        $this->comment_meta = $comment_meta;
    }

    public function store(Request $request)
    {
        $validate = \Validator::make($request->all(), [
            'stars' => 'required',
            'title' => 'required',
            'body' => 'required',
            'post_id' => 'required',
            'user_id' => 'required',
        ]);
        if ($validate->fails()) {
            return response(['status' => 0, 'data' => array(), 'message' => $validate->errors()], 422);
        }
        $is_rate = $this->comment->where('comment_approved', '!=', 'trash')->where('comment_post_ID', $request->post_id)->where('user_id', $request->user_id)->count();
        if ($is_rate == 0) {
            $user = $this->user->find($request->user_id);
            if ($user) {
                $review = new $this->comment;
                $review->comment_post_ID = $request->post_id;
                $review->comment_author = $user->user_login;
                $review->comment_author_email = $user->user_email;
                $review->comment_author_url = ' ';
                $review->comment_author_IP = '127.0.0.1';
                $review->comment_date = date("Y-m-d H:i:s");
                $review->comment_date_gmt = date("Y-m-d H:i:s");
                $review->comment_content = $request->body;
                $review->comment_karma = '0';
                $review->comment_approved = '0';
                $review->comment_type = 'comment';
                $review->comment_parent = '0';
                $review->user_id = $request->user_id;
                $review->comment_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.10) Gecko/2009042316 Firefox/3.0.10 (.NET CLR 3.5.30729)';
                $review->save();
                $data_meta = array('review_stars' => $request->stars,
                    'review_title' => $request->title,
                );
                foreach ($data_meta as $key => $value) {
                    $review_meta = new $this->comment_meta;
                    $review_meta->comment_id = $review->comment_ID;
                    $review_meta->meta_key = $key;
                    $review_meta->meta_value = $value;
                    $review_meta->save();
                }
                return response()->json(['status' => '1', 'data' => array(), 'message' => 'Message_Done'], 200);
            }
            return response()->json(['status' => '0', 'data' => array(), 'message' => 'user id not found'], 400);
        }
        return response()->json(['status' => '0', 'data' => array(), 'message' => 'You Have Already Evaluated'], 400);
    }

    public function index(Request $request)
    {
        $review=$this->comment->where('comment_approved','1')->where('comment_post_ID', $request->post_id)->get();
        foreach ($review as $reviews)
        {
            $reviews->user=$this->user->find($reviews->user_id);
        }
        $is_rate = $this->comment->where('comment_approved', '!=', 'trash')->where('comment_post_ID', $request->post_id)->where('user_id', $request->user_id)->count();
        if ($is_rate>0) {
            $is_rate = 1;
        }else
        {
            $is_rate = 0;
        }
        return response()->json(['status' => '1', 'data' => ReviewResource::collection($review),'is_rate'=>$is_rate, 'message' => 'Message_Done'], 200);
    }
}