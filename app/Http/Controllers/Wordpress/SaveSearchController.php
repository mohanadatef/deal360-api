<?php

namespace App\Http\Controllers\Wordpress;

use App\Models\Wordpress\PostWordpress;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SaveSearchController extends Controller
{
    protected $post;

    public function __construct(PostWordpress $post)
    {
        $this->post = $post;
    }

    public function delete(Request $request)
    {
        $post = $this->post->find($request->post_id);
        if ($post) {
            foreach ($post->meta as $meta) {
                $meta->delete();
            }
            $post->delete();
            return response()->json(['status' => '1', 'data' => array(), 'message' => 'Successful'], 200);
        }
        return response()->json(['status' => '0', 'data' => array(), 'message' => 'Post ID does not exists or was deleted'], 400);
    }
}